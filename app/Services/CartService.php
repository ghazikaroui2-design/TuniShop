<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Models\Cart;

/**
 * Service métier du panier (US-035, US-036, RG-01, RG-10).
 * Le contrôle d'intégrité du stock reste dans la procédure + le trigger SQL.
 */
final class CartService
{
    public function __construct(
        private readonly Database $database,
        private readonly Session $session,
        private readonly Cart $carts,
    ) {
    }

    /** Panier actif de la session (connecté ou invité), créé si nécessaire. */
    public function currentCartId(): int
    {
        $userId = $this->session->userId();

        if ($userId !== null) {
            $cart = $this->carts->findForUser($userId);
            if ($cart !== null) {
                return (int) $cart['id'];
            }

            $this->database->execute(
                'INSERT INTO carts (user_id, status, expires_at)'
                . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
                [$userId]
            );

            return (int) $this->database->pdo()->lastInsertId();
        }

        $token = $this->session->get('guest_cart_token');
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->session->set('guest_cart_token', $token);
        }

        $cart = $this->carts->findByToken($token);
        if ($cart !== null) {
            return (int) $cart['id'];
        }

        $this->database->execute(
            'INSERT INTO carts (guest_token, status, expires_at)'
            . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
            [$token]
        );

        return (int) $this->database->pdo()->lastInsertId();
    }

    /** RG-01 : sp_add_to_cart vérifie le stock et lève une erreur si besoin. */
    public function add(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            return;
        }

        $this->database->execute(
            'CALL sp_add_to_cart(?, ?, ?)',
            [$this->currentCartId(), $productId, $quantity]
        );
    }

    public function remove(int $cartId, int $productId): void
    {
        $this->carts->removeItem($cartId, $productId);
    }

    public function items(int $cartId): array
    {
        return $this->carts->items($cartId);
    }

    /** RG-02 / RG-03 : calcul centralisé par la vue SQL v_cart_totals. */
    public function totals(int $cartId): array
    {
        $totals = $this->carts->totals($cartId);

        if ($totals === null) {
            return [
                'subtotal_ttc' => 0.0, 'subtotal_ht' => 0.0, 'vat_amount' => 0.0,
                'shipping_fee' => 0.0, 'total_ttc' => 0.0, 'remaining_for_free_shipping' => 100.0,
            ];
        }

        $totals['total_ttc'] = round(
            (float) $totals['subtotal_ttc'] + (float) $totals['shipping_fee'],
            2
        );

        return $totals;
    }
}
