<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    protected string $table = 'categories';

    public function active(): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name'
        );
    }
}
