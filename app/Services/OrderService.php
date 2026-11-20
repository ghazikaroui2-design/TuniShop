<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

/** Service métier de la commande (US-037) — délègue la transaction à sp_place_order. */
final class OrderService
{
    public function __construct(
        private readonly Database $database,
        private readonly Session $session,
        private readonly CartService $carts,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{p_order_id:mixed, p_order_number:mixed}
     */
    public function place(array $input): array
    {
        $mode = ($input['delivery_mode'] ?? 'domicile') === 'retrait' ? 'retrait' : 'domicile';
        $addressId = !empty($input['address_id']) ? (int) $input['address_id'] : null;
        $promo = !empty($input['promo_code']) ? strtoupper(trim((string) $input['promo_code'])) : null;
        $guestEmail = $this->session->isAuthenticated()
            ? null
            : (is_string($input['guest_email'] ?? null) ? $input['guest_email'] : null);

        $result = $this->database->call(
            'sp_place_order',
            [
                $this->carts->currentCartId(),
                $this->session->userId(),
                $guestEmail,
                $mode,
                $addressId,
                $promo,
            ],
            ['p_order_id', 'p_order_number']
        );

        AuditService::log(
            'ORDER_PLACED',
            'orders',
            (string) ($result['p_order_id'] ?? ''),
            (string) ($result['p_order_number'] ?? '')
        );

        return $result;
    }
}
