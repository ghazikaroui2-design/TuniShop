<?php

declare(strict_types=1);

namespace App\Models;

/** Adresses de livraison du client (FR-11, étape 2 du checkout). */
final class Address extends Model
{
    protected string $table = 'addresses';

    /** @return list<array<string, mixed>> */
    public function forUser(int $userId): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM addresses WHERE user_id = ?'
            . ' ORDER BY is_default DESC, label ASC, id ASC',
            [$userId]
        );
    }

    public function belongsToUser(int $addressId, int $userId): bool
    {
        return $this->database->scalar(
            'SELECT COUNT(*) FROM addresses WHERE id = ? AND user_id = ?',
            [$addressId, $userId]
        ) > 0;
    }
}
