<?php

declare(strict_types=1);

namespace App\Models;

final class Cart extends Model
{
    protected string $table = 'carts';

    public function findForUser(int $userId): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM carts WHERE user_id = ? AND status = "actif" ORDER BY id DESC LIMIT 1',
            [$userId]
        );
    }

    public function findByToken(string $token): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM carts WHERE guest_token = ? AND status = "actif" ORDER BY id DESC LIMIT 1',
            [$token]
        );
    }

    public function items(int $cartId): array
    {
        return $this->database->fetchAll(
            'SELECT ci.*, p.image_path AS primary_image'
            . ' FROM cart_items ci'
            . ' LEFT JOIN product_images p ON p.product_id = ci.product_id AND p.is_primary = 1'
            . ' WHERE ci.cart_id = ? ORDER BY ci.id',
            [$cartId]
        );
    }

    public function totals(int $cartId): ?array
    {
        return $this->database->fetch('SELECT * FROM v_cart_totals WHERE cart_id = ?', [$cartId]);
    }

    public function removeItem(int $cartId, int $productId): void
    {
        $this->database->execute(
            'DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?',
            [$cartId, $productId]
        );
    }
}
