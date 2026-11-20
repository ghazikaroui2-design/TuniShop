<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;

/** Tunnel de commande en 3 étapes (FR-11 à FR-13, RG-04, RG-07, RG-08). */
final class OrderController extends Controller
{
    private function cartService(): CartService
    {
        return new CartService($this->database, $this->session, new Cart($this->database));
    }

    public function checkout(Request $request): Response
    {
        $service = $this->cartService();
        $cartId = $service->currentCartId();
        $items = $service->items($cartId);

        if ($items === []) {
            $this->session->flash('error', 'Votre panier est vide.');

            return $this->redirect('/panier');
        }

        return $this->render('checkout/index', [
            'pageTitle' => 'Finaliser ma commande',
            'cartId'    => $cartId,
            'items'     => $items,
            'totals'    => $service->totals($cartId),
            'csrf'      => $this->session->csrfToken(),
        ]);
    }

    /**
     * Étape 3 : création de la commande puis simulation du paiement.
     * Dans un vrai système, le passage en « payée » viendrait d'un webhook.
     */
    public function place(Request $request): Response
    {
        $orderService = new OrderService($this->database, $this->session, $this->cartService());
        $result = $orderService->place($request->post);

        $orderId = (int) ($result['p_order_id'] ?? 0);
        if ($orderId > 0) {
            (new PaymentService($this->database))->simulate($orderId);
        }

        $this->session->flash('success', 'Commande ' . ($result['p_order_number'] ?? '') . ' confirmée !');

        return $this->redirect('/commande/' . $orderId);
    }

    public function confirmation(Request $request, int $id): Response
    {
        $orders = new Order($this->database);
        $order = $orders->find($id);

        if ($order === null || (int) $order['user_id'] !== $this->session->userId()) {
            throw new NotFoundException('Commande introuvable.');
        }

        return $this->render('order/confirmation', [
            'pageTitle' => 'Commande ' . $order['order_number'],
            'order'     => $order,
            'items'     => $orders->items($id),
            'invoice'   => $orders->invoice($id),
            'history'   => $orders->history($id),
        ]);
    }
}
