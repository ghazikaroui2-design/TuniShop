<?php

declare(strict_types=1);

namespace App\Models;

final class Shop extends Model
{
    protected string $table = 'shops';

    public function pending(): array
    {
        return $this->database->fetchAll(
            'SELECT s.*, u.first_name, u.last_name, u.email AS owner_email'
            . ' FROM shops s JOIN users u ON u.id = s.owner_id'
            . ' WHERE s.status = "en_attente" ORDER BY s.submitted_at'
        );
    }

    public function validated(): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM shops WHERE status = "validée" ORDER BY name'
        );
    }
}
