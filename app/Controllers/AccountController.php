<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;

/** Espace client (écran 7) — protégé par le middleware auth. */
final class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $this->session->userId();
        if ($userId === null) {
            return $this->redirect('/connexion');
        }

        $user = (new User($this->database))->find($userId) ?? $this->session->user() ?? [];
        $orders = (new Order($this->database))->forUser($userId, 50);
        $addresses = (new Address($this->database))->forUser($userId);

        $reviewCount = (int) $this->database->scalar(
            'SELECT COUNT(*) FROM reviews WHERE user_id = ?',
            [$userId]
        );
        $spent = (float) $this->database->scalar(
            "SELECT COALESCE(SUM(total_ttc), 0) FROM orders WHERE user_id = ?"
            . " AND status IN ('payée', 'en_préparation', 'expédiée', 'livrée')",
            [$userId]
        );
        $delivered = array_values(array_filter(
            $orders,
            static fn (array $order): bool => ($order['status'] ?? '') === 'livrée'
        ));

        return $this->render('account/index', [
            'pageTitle'       => 'Mon compte',
            'pageDescription' => 'Suivez vos commandes, vos factures et vos adresses de livraison TuniShop.',
            'breadcrumb'      => ['Mon compte' => null],
            'user'            => $user,
            'orders'          => $orders,
            'addresses'       => $addresses,
            'delivered'       => $delivered,
            'reviewCount'     => $reviewCount,
            'spent'           => $spent,
            'csrf'            => $this->session->csrfToken(),
        ]);
    }
}
