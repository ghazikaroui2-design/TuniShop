<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Cart;
use App\Services\CartService;
use PDOException;

final class CartController extends Controller
{
    private function service(): CartService
    {
        return new CartService($this->database, $this->session, new Cart($this->database));
    }

    public function index(Request $request): Response
    {
        $service = $this->service();
        $cartId = $service->currentCartId();

        return $this->render('cart/index', [
            'pageTitle' => 'Mon panier',
            'cartId'    => $cartId,
            'items'     => $service->items($cartId),
            'totals'    => $service->totals($cartId),
            'csrf'      => $this->session->csrfToken(),
            'flash'     => [
                'success' => $this->session->flash('success'),
                'error'   => $this->session->flash('error'),
            ],
        ]);
    }

    /** RG-01 : les erreurs de stock remontent du trigger SQL (SQLSTATE 45000). */
    public function add(Request $request): Response
    {
        try {
            $this->service()->add(
                (int) $request->input('product_id', 0),
                (int) $request->input('quantity', 1)
            );
            $this->session->flash('success', 'Produit ajouté au panier.');
        } catch (PDOException) {
            $this->session->flash('error', 'Stock insuffisant ou produit indisponible.');
        }

        return $this->redirect('/panier');
    }

    public function remove(Request $request): Response
    {
        $service = $this->service();
        $service->remove($service->currentCartId(), (int) $request->input('product_id', 0));
        $this->session->flash('success', 'Article retiré du panier.');

        return $this->redirect('/panier');
    }
}
