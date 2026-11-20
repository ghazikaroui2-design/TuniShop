<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\ValidationException;

/** Validation / refus d'une boutique (RG-06, US-041). */
final class ShopService
{
    public function __construct(
        private readonly Database $database,
        private readonly Session $session,
    ) {
    }

    public function validate(int $shopId, string $decision, ?string $reason = null): void
    {
        if (!in_array($decision, ['validée', 'refusée'], true)) {
            throw new ValidationException(['decision' => 'Décision invalide (validée ou refusée attendu).']);
        }

        $this->database->execute(
            'CALL sp_validate_shop(?, ?, ?, ?)',
            [$shopId, $this->session->userId(), $decision, $reason]
        );
    }
}
