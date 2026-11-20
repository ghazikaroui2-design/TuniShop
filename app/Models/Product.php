<?php

declare(strict_types=1);

namespace App\Models;

/** Produits du catalogue (lit la vue v_catalogue : publies + boutiques validees). */
final class Product extends Model
{
    protected string $table = 'products';

    /** @param array<string, mixed> $filters */
    public function catalogue(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $sql = 'SELECT * FROM v_catalogue WHERE 1 = 1';
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= ' AND category_slug = ?';
            $params[] = (string) $filters['category'];
        }
        if (!empty($filters['shop'])) {
            $sql .= ' AND shop_slug = ?';
            $params[] = (string) $filters['shop'];
        }
        if (!empty($filters['q'])) {
            $needle = '%' . trim((string) $filters['q']) . '%';
            $sql .= ' AND (name LIKE ? OR short_description LIKE ?)';
            $params[] = $needle;
            $params[] = $needle;
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $sql .= ' AND price_ttc <= ?';
            $params[] = (float) $filters['max_price'];
        }
        if (!empty($filters['featured'])) {
            $sql .= ' AND is_featured = 1';
        }

        $sql .= ' ORDER BY is_featured DESC, name ASC LIMIT ' . max(1, $limit)
            . ' OFFSET ' . max(0, $offset);

        return $this->database->fetchAll($sql, $params);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->database->fetch('SELECT * FROM v_catalogue WHERE slug = ? LIMIT 1', [$slug]);
    }

    public function lowStock(): array
    {
        return $this->database->fetchAll('SELECT * FROM v_low_stock');
    }

    public function images(int $productId): array
    {
        return $this->database->fetchAll(
            'SELECT image_path, alt_text, is_primary, sort_order FROM product_images'
            . ' WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC',
            [$productId]
        );
    }
}
