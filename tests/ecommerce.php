<?php

declare(strict_types=1);

/**
 * TuniShop — Recette du module E-commerce (US-035 à US-038, US-044, FR-26).
 * Exécution : composer test:ecommerce   (ou php tests/ecommerce.php)
 * Prérequis : base `tunishop` importée et dépendances Composer installées.
 *
 * Les tests créent des paniers/commandes temporaires et les suppriment
 * systématiquement : la base de démonstration reste inchangée.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\InvoiceService;
use App\Services\PaymentService;

$root = dirname(__DIR__);
$appConfig = require $root . '/config/app.php';
$dbConfig  = require $root . '/config/database.php';
$routes    = require $root . '/config/routes.php';

$app = new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);
$db = $app->database;

final class EcommerceTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function check(string $name, callable $assertion): void
    {
        try {
            $result = $assertion();
            if ($result === true) {
                $this->passed++;
                echo "PASS  $name\n";
            } else {
                $this->failed++;
                echo 'FAIL  ' . $name . ' — ' . var_export($result, true) . "\n";
            }
        } catch (Throwable $e) {
            $this->failed++;
            echo 'FAIL  ' . $name . ' — ' . get_class($e) . ' : ' . $e->getMessage() . "\n";
        }
    }

    public function finish(): int
    {
        echo "\n=== {$this->passed} PASS / {$this->failed} FAIL ===\n";

        return $this->failed === 0 ? 0 : 1;
    }
}

$t = new EcommerceTest();

$server = [
    'REQUEST_METHOD'  => 'GET',
    'REQUEST_URI'     => '/',
    'REMOTE_ADDR'     => '127.0.0.1',
    'HTTP_USER_AGENT' => 'TuniShop-Ecommerce-Test/1.0',
];

/** Construit une requête CLI. */
$req = static function (string $method, string $path, array $query = [], array $post = []) use ($server): Request {
    return new Request($method, $path, $query, $post, $server + ['REQUEST_METHOD' => $method]);
};

$session = $app->session;
$session->setUser([
    'id' => 2, 'role' => 'client', 'email' => 'aymen.benali@tunishop.tn',
    'first_name' => 'Aymen', 'last_name' => 'Ben Ali',
]);

// ---------------------------------------------------------------------
// 1. Dépendance PDF et routage
// ---------------------------------------------------------------------
$t->check('E01 — DomPDF installé (dépendance Composer)', static fn () =>
    class_exists(\Dompdf\Dompdf::class)
    && class_exists(\Dompdf\Options::class)
);

$t->check('E02 — Route nommée order.invoice déclarée', static fn () =>
    in_array(['GET', '/commande/{id}/facture.pdf', 'App\\Controllers\\OrderController@invoice',
        ['auth'], 'order.invoice'], $routes, true)
    || (function () use ($routes): bool {
        foreach ($routes as $route) {
            if (($route[4] ?? null) === 'order.invoice') {
                return $route[0] === 'GET' && $route[1] === '/commande/{id}/facture.pdf';
            }
        }

        return false;
    })()
);

// ---------------------------------------------------------------------
// 2. Checkout 3 étapes
// ---------------------------------------------------------------------
$t->check('E03 — Étape 1 : la livraison est validée et stockée en session', static function () use ($app, $req, $session) {
    $token = $session->csrfToken();
    $response = $app->handle($req('POST', '/commande', [], [
        '_token' => $token, 'etape' => '1',
        'delivery_mode' => 'domicile', 'address_id' => '1',
    ]));
    $checkout = $session->get('checkout');

    return $response->status === 302
        && ($response->headers['Location'] ?? null) === '/commande?etape=2'
        && is_array($checkout)
        && $checkout['delivery_mode'] === 'domicile'
        && $checkout['address_id'] === 1;
});

$t->check('E04 — Étape 2 : le formulaire sandbox est rendu sans données bancaires', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/commande', ['etape' => '2']));
    $body = $response->body;

    return $response->status === 200
        && str_contains($body, '4242 4242 4242 4242')
        && str_contains($body, 'ARTISAN5')
        && str_contains($body, 'confirm_sandbox')
        && !preg_match('/<input[^>]+name="card_number"/i', $body)
        && !preg_match('/<input[^>]+name="card_cvc"/i', $body);
});

$t->check('E05 — RG-07 : carte de test reconnue, carte arbitraire refusée', static fn () =>
    (new PaymentService($db))->isValidTestCard('4242 4242 4242 4242')
    && !(new PaymentService($db))->isValidTestCard('4111 1111 1111 1111')
);

// ---------------------------------------------------------------------
// 3. Panier : modification de quantité et garde-fou RG-01
// ---------------------------------------------------------------------
$t->check('E06 — FR-08 : modifier une quantité recalcule les totaux', static function () use ($db, $app) {
    $token = 'ec_qty_' . bin2hex(random_bytes(8));
    $db->execute(
        'INSERT INTO carts (guest_token, status, expires_at)'
        . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
        [$token]
    );
    $cartId = (int) $db->pdo()->lastInsertId();

    try {
        $service = new CartService($db, $app->session, new Cart($db));
        $service->updateQuantity($cartId, 1, 1);
        $service->updateQuantity($cartId, 1, 3);
        $row = $db->fetch('SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = 1', [$cartId]);
        $totals = $service->totals($cartId);

        return (int) ($row['quantity'] ?? 0) === 3
            && (float) $totals['subtotal_ttc'] === 144.00
            && (float) $totals['vat_amount'] === 22.99; // 144 − 121,01 (TVA 19 % incluse)
    } finally {
        $db->execute('DELETE FROM carts WHERE id = ?', [$cartId]);
    }
});

$t->check('E07 — RG-01 : quantité supérieure au stock refusée', static function () use ($db, $app) {
    $token = 'ec_stock_' . bin2hex(random_bytes(8));
    $db->execute(
        'INSERT INTO carts (guest_token, status, expires_at)'
        . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
        [$token]
    );
    $cartId = (int) $db->pdo()->lastInsertId();

    try {
        (new CartService($db, $app->session, new Cart($db)))->updateQuantity($cartId, 1, 9999);

        return false;
    } catch (PDOException) {
        return true;
    } finally {
        $db->execute('DELETE FROM carts WHERE id = ?', [$cartId]);
    }
});

// ---------------------------------------------------------------------
// 4. Commande, code promo, paiement et facture
// ---------------------------------------------------------------------
$t->check('E08 — FR-10 : code promo ARTISAN5 (−5 DT) appliqué au checkout', static function () use ($db) {
    $token = 'ec_promo_' . bin2hex(random_bytes(8));
    $db->execute(
        'INSERT INTO carts (guest_token, status, expires_at)'
        . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
        [$token]
    );
    $cartId = (int) $db->pdo()->lastInsertId();
    $orderId = 0;

    try {
        $db->execute('CALL sp_add_to_cart(?, ?, ?)', [$cartId, 1, 1]);
        $result = $db->call(
            'sp_place_order',
            [$cartId, null, 'test@tunishop.tn', 'domicile', null, 'ARTISAN5'],
            ['p_order_id', 'p_order_number']
        );
        $orderId = (int) ($result['p_order_id'] ?? 0);
        $order = (new Order($db))->find($orderId);

        return $orderId > 0
            && (float) ($order['discount_amount'] ?? -1) === 5.00
            && (float) ($order['total_ttc'] ?? -1) === 51.00
            && str_starts_with((string) ($result['p_order_number'] ?? ''), 'CMD-' . date('Y') . '-');
    } finally {
        if ($orderId > 0) {
            $db->execute("UPDATE orders SET status = 'annulée' WHERE id = ?", [$orderId]);
            $db->execute('DELETE FROM orders WHERE id = ?', [$orderId]);
        }
        $db->execute('DELETE FROM carts WHERE id = ?', [$cartId]);
        $db->execute(
            'UPDATE promo_codes SET uses_count = GREATEST(uses_count - 1, 0) WHERE code = ?',
            ['ARTISAN5']
        );
    }
});

$t->check('E09 — RG-07/RG-08 : paiement sandbox génère référence SBX et facture F-YYYY-NNNN', static function () use ($db) {
    $token = 'ec_pay_' . bin2hex(random_bytes(8));
    $db->execute(
        'INSERT INTO carts (guest_token, status, expires_at)'
        . ' VALUES (?, "actif", DATE_ADD(NOW(), INTERVAL 30 DAY))',
        [$token]
    );
    $cartId = (int) $db->pdo()->lastInsertId();
    $orderId = 0;

    try {
        $db->execute('CALL sp_add_to_cart(?, ?, ?)', [$cartId, 1, 1]);
        $result = $db->call(
            'sp_place_order',
            [$cartId, null, 'test@tunishop.tn', 'domicile', null, null],
            ['p_order_id', 'p_order_number']
        );
        $orderId = (int) ($result['p_order_id'] ?? 0);
        $ref = (new PaymentService($db))->simulate($orderId);
        $order = (new Order($db))->find($orderId);
        $invoice = (new Order($db))->invoice($orderId);

        return str_starts_with($ref, 'SBX-' . date('Y') . '-')
            && $order['status'] === 'payée'
            && $order['transaction_ref'] === $ref
            && preg_match('/^F-\d{4}-\d{4}$/', (string) ($invoice['invoice_number'] ?? '')) === 1
            && (float) $invoice['total_ttc'] === 56.00; // 48 + 8 livraison
    } finally {
        if ($orderId > 0) {
            $db->execute("UPDATE orders SET status = 'annulée' WHERE id = ?", [$orderId]);
            $db->execute('DELETE FROM orders WHERE id = ?', [$orderId]);
        }
        $db->execute('DELETE FROM carts WHERE id = ?', [$cartId]);
    }
});

$t->check('E10 — RG-07 : aucune colonne bancaire dans la table payments', static function () use ($db) {
    $columns = array_column($db->fetchAll(
        "SELECT column_name FROM information_schema.columns"
        . " WHERE table_schema='tunishop' AND table_name='payments'"
    ), 'column_name');

    return !in_array('card_number', $columns, true)
        && !in_array('cvc', $columns, true)
        && !in_array('expiry', $columns, true);
});

// ---------------------------------------------------------------------
// 5. Facture PDF (FR-26)
// ---------------------------------------------------------------------
$t->check('E11 — FR-26 : le HTML de facture contient HT, TVA, TTC et la référence', static function () use ($app, $db) {
    $service = new InvoiceService(
        $db, $app->view, dirname(__DIR__) . '/public/factures'
    );
    $html = $service->renderHtml(1);

    return str_contains($html, 'F-2026-0001')
        && str_contains($html, '72,27 DT')
        && str_contains($html, '13,73 DT')
        && str_contains($html, '94,00 DT')
        && str_contains($html, 'SBX-');
});

$t->check('E12 — FR-26 : DomPDF produit un fichier %PDF valide', static function () use ($app, $db) {
    $service = new InvoiceService(
        $db, $app->view, dirname(__DIR__) . '/public/factures'
    );
    $pdf = $service->renderPdf(1);

    return str_starts_with($pdf, '%PDF-') && strlen($pdf) > 2000;
});

$t->check('E13 — FR-26 : GET /commande/1/facture.pdf télécharge un PDF', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/commande/1/facture.pdf'));

    return $response->status === 200
        && ($response->headers['Content-Type'] ?? '') === 'application/pdf'
        && str_contains($response->headers['Content-Disposition'] ?? '', 'F-2026-0001.pdf')
        && str_starts_with($response->body, '%PDF-');
});

$t->check('E14 — RG-08 : invoices.pdf_path renseigné après génération', static function () use ($db) {
    $path = $db->scalar('SELECT pdf_path FROM invoices WHERE order_id = 1');

    return is_string($path) && str_starts_with($path, 'factures/') && str_ends_with($path, '.pdf');
});

$t->check('E15 — Accès refusé à la facture d’un autre client', static function () use ($app, $req, $session) {
    $session->setUser([
        'id' => 3, 'role' => 'client', 'email' => 'fatma@tunishop.tn',
        'first_name' => 'Fatma', 'last_name' => 'Test',
    ]);

    try {
        $app->handle($req('GET', '/commande/1/facture.pdf'));

        return false;
    } catch (\App\Exceptions\NotFoundException $e) {
        return $e->status() === 404;
    } finally {
        $session->setUser([
            'id' => 2, 'role' => 'client', 'email' => 'aymen.benali@tunishop.tn',
            'first_name' => 'Aymen', 'last_name' => 'Ben Ali',
        ]);
    }
});

exit($t->finish());
