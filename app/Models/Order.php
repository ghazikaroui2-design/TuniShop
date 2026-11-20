<?php

declare(strict_types=1);

namespace App\Models;

final class Order extends Model
{
    protected string $table = 'orders';

    public function forUser(int $userId, int $limit = 20): array
    {
        return $this->database->fetchAll(
            'SELECT o.id, o.order_number, o.placed_at, o.status, o.total_ttc, o.delivery_mode,'
            . ' i.invoice_number,'
            . ' (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count'
            . ' FROM orders o LEFT JOIN invoices i ON i.order_id = o.id'
            . ' WHERE o.user_id = ? ORDER BY o.placed_at DESC LIMIT ' . max(1, $limit),
            [$userId]
        );
    }

    public function findByNumber(string $number): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM orders WHERE order_number = ? LIMIT 1',
            [$number]
        );
    }

    public function items(int $orderId): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM order_items WHERE order_id = ? ORDER BY id',
            [$orderId]
        );
    }

    public function invoice(int $orderId): ?array
    {
        return $this->database->fetch('SELECT * FROM invoices WHERE order_id = ? LIMIT 1', [$orderId]);
    }

    public function history(int $orderId): array
    {
        return $this->database->fetchAll(
            'SELECT from_status, to_status, note, created_at FROM order_status_history'
            . ' WHERE order_id = ? ORDER BY id',
            [$orderId]
        );
    }
}
