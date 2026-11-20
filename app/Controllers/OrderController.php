<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\PaymentService;

/**
 * Tunnel de commande en 3 étapes (FR-11 à FR-15, RG-04, RG-07, RG-08).
 *
 * Étape 1 — livraison  : GET /commande, POST /commande (etape=1)
 * Étape 2 — paiement   : GET /commande?etape=2, POST /commande (etape=2)
 * Étape 3 — confirmation: GET /commande/{id} + facture PDF
 */
final class OrderController extends Controller
{
    private function cartService(): CartService
    {
        return new CartService($this->database, $this->session, new Cart($this->database));
    }

    private function invoiceService(): InvoiceService
    {
        return new InvoiceService(
            $this->database,
            $this->view,
            dirname(__DIR__, 2) . '/public/factures'
        );
    }

    /** Étape 1 (livraison) ou étape 2 (paiement), pilotée par ?etape=2. */
    public function checkout(Request $request): Response
    {
        $service = $this->cartService();
        $cartId = $service->currentCartId();
        $items = $service->items($cartId);

        if ($items === []) {
            $this->session->flash('error', 'Votre panier est vide.');

            return $this->redirect('/panier');
        }

        $step = (int) $request->query('etape', 1) === 2 ? 2 : 1;
        $checkout = $this->session->get('checkout');

        if ($step === 2 && (!is_array($checkout) || empty($checkout['delivery_mode']))) {
            $this->session->flash('error', 'Choisissez d’abord votre mode de livraison.');

            return $this->redirect('/commande');
        }

        $addresses = [];
        if ($this->session->userId() !== null) {
            $addresses = (new Address($this->database))->forUser($this->session->userId());
        }

        return $this->render('checkout/index', [
            'pageTitle' => $step === 1 ? 'Livraison' : 'Paiement',
            'step'      => $step,
            'cartId'    => $cartId,
            'items'     => $items,
            'totals'    => $service->totals($cartId),
            'addresses' => $addresses,
            'checkout'  => is_array($checkout) ? $checkout : [],
            'csrf'      => $this->session->csrfToken(),
            'flash'     => [
                'success' => $this->session->flash('success'),
                'error'   => $this->session->flash('error'),
            ],
        ]);
    }

    /**
     * POST /commande :
     *  - etape=1 → validation de la livraison, stockage en session, redirection étape 2 ;
     *  - etape=2 → création de la commande puis simulation du paiement.
     */
    public function place(Request $request): Response
    {
        if ($request->input('etape') === '1' || $request->input('etape') === 1) {
            return $this->saveDelivery($request);
        }

        $checkout = $this->session->get('checkout');
        if (!is_array($checkout) || empty($checkout['delivery_mode'])) {
            $this->session->flash('error', 'Votre session de commande a expiré.');

            return $this->redirect('/commande');
        }

        // RG-07 : l'utilisateur confirme utiliser la carte de test. Les champs
        // de carte n'ont pas d'attribut name : aucune donnée bancaire n'atteint
        // le serveur, et encore moins la base de données.
        if (!$request->input('confirm_sandbox')) {
            $this->session->flash('error', 'Veuillez confirmer le paiement sandbox.');

            return $this->redirect('/commande?etape=2');
        }

        $input = $request->post;
        $input['delivery_mode'] = $checkout['delivery_mode'];
        $input['address_id']    = $checkout['address_id'] ?? null;

        $orderService = new OrderService($this->database, $this->session, $this->cartService());

        try {
            $result = $orderService->place($input);
        } catch (\PDOException) {
            $this->session->flash('error', 'Le panier a été modifié ou le stock est insuffisant.');

            return $this->redirect('/panier');
        }

        $orderId = (int) ($result['p_order_id'] ?? 0);
        if ($orderId > 0) {
            (new PaymentService($this->database))->simulate($orderId);
        }

        $this->session->forget('checkout');
        $this->session->flash('success', 'Commande ' . ($result['p_order_number'] ?? '') . ' confirmée !');

        return $this->redirect('/commande/' . $orderId);
    }

    /** Étape 1 : mémorise le mode de livraison et l'adresse choisie. */
    private function saveDelivery(Request $request): Response
    {
        $mode = (string) $request->input('delivery_mode', 'domicile');
        if (!in_array($mode, ['domicile', 'retrait'], true)) {
            $this->session->flash('error', 'Mode de livraison invalide.');

            return $this->redirect('/commande');
        }

        $addressId = null;
        if ($mode === 'domicile') {
            $raw = $request->input('address_id');
            if (!is_scalar($raw) || !ctype_digit((string) $raw)) {
                $this->session->flash('error', 'Veuillez choisir une adresse de livraison.');

                return $this->redirect('/commande');
            }

            $addressId = (int) $raw;
            $userId = $this->session->userId();
            if ($userId === null || !(new Address($this->database))->belongsToUser($addressId, $userId)) {
                $this->session->flash('error', 'Cette adresse ne vous appartient pas.');

                return $this->redirect('/commande');
            }
        }

        $this->session->set('checkout', [
            'delivery_mode' => $mode,
            'address_id'    => $addressId,
        ]);

        return $this->redirect('/commande?etape=2');
    }

    /** Étape 3 : confirmation de la commande. */
    public function confirmation(Request $request, int $id): Response
    {
        $order = $this->loadOrderForCurrentUser($id);

        return $this->render('order/confirmation', [
            'pageTitle' => 'Commande ' . $order['order_number'],
            'order'     => $order,
            'items'     => (new Order($this->database))->items($id),
            'invoice'   => (new Order($this->database))->invoice($id),
            'history'   => (new Order($this->database))->history($id),
        ]);
    }

    /** FR-26 / RG-08 : téléchargement de la facture PDF. */
    public function invoice(Request $request, int $id): Response
    {
        $order = $this->loadOrderForCurrentUser($id);
        if ($order['status'] === 'en_attente') {
            throw new ForbiddenException('La facture est disponible après paiement.');
        }

        $service = $this->invoiceService();
        $path = $service->save($id);
        $pdf = file_get_contents($path);
        if ($pdf === false) {
            throw new \RuntimeException('Lecture de la facture impossible.');
        }

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $service->filename($id) . '"',
            'Content-Length'      => (string) strlen($pdf),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** @return array<string, mixed> */
    private function loadOrderForCurrentUser(int $id): array
    {
        $order = (new Order($this->database))->find($id);
        $isOwner = $order !== null && (int) $order['user_id'] === $this->session->userId();
        $isAdmin = $this->session->role() === 'admin';

        if (!$isOwner && !$isAdmin) {
            // On ne révèle pas l'existence d'une commande appartenant à autrui.
            throw new NotFoundException('Commande introuvable.');
        }
        if ($order === null) {
            throw new NotFoundException('Commande introuvable.');
        }

        return $order;
    }
}
