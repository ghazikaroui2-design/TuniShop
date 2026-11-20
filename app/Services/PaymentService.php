<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Paiement sandbox (RG-07).
 * ⚠️ Aucun numéro de carte, date d'expiration ni CVC n'est transmis à la BDD
 * et encore moins conservé. La carte de test sert uniquement d'aide visuelle.
 */
final class PaymentService
{
    private const TEST_CARD = '4242424242424242';

    public function __construct(private readonly Database $database)
    {
    }

    /** Validation cosmétique de la carte de démonstration (jamais persistée). */
    public function isValidTestCard(string $number): bool
    {
        return preg_replace('/\D+/', '', $number) === self::TEST_CARD;
    }

    /** @return string Référence factice SBX-YYYY-NNNNNN */
    public function simulate(int $orderId): string
    {
        $result = $this->database->call(
            'sp_simulate_payment',
            [$orderId],
            ['p_transaction_ref']
        );

        return (string) ($result['p_transaction_ref'] ?? '');
    }
}
