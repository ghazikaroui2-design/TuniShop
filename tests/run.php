<?php

declare(strict_types=1);

/**
 * TuniShop — Tests de recette du socle MVC (US-025).
 * Exécution : composer test   (ou php tests/run.php)
 * Prérequis : base tunishop importée via database/*.sql.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\HomeController;
use App\Core\Application;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ForbiddenException;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RoleMiddleware;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PaymentService;

$root = dirname(__DIR__);

final class SmokeTest
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

$t = new SmokeTest();

$appConfig = require $root . '/config/app.php';
$dbConfig  = require $root . '/config/database.php';
$routes    = require $root . '/config/routes.php';

$app = new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);

/** Fabrique une requête de test. */
$req = static function (string $method, string $path, array $post = [], array $query = []) use ($dbConfig): Request {
    return new Request($method, $path, $query, $post, [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI'    => $path,
        'REMOTE_ADDR'    => '127.0.0.1',
        'HTTP_USER_AGENT'=> 'TuniShop-CLI-Test/1.0',
    ]);
};

// ---------------------------------------------------------------------
// 1. Autoload PSR-4 et structure
// ---------------------------------------------------------------------
$t->check('T01 — Autoload PSR-4 des classes App\\', static fn () =>
    class_exists(\App\Core\Router::class)
    && class_exists(HomeController::class)
    && class_exists(\App\Services\OrderService::class)
);

$t->check('T02 — 26 routes déclarées', static fn () => count($routes) === 26);

$t->check('T02b — Routes frontend /compte et /vendeur/boutique protégées', static function () use ($routes) {
    $find = static function (string $method, string $path) use ($routes): ?array {
        foreach ($routes as $route) {
            if ($route[0] === $method && $route[1] === $path) {
                return $route;
            }
        }

        return null;
    };

    $account = $find('GET', '/compte');
    $shopGet = $find('GET', '/vendeur/boutique');
    $shopPost = $find('POST', '/vendeur/boutique');

    return $account !== null
        && in_array('auth', $account[3], true)
        && $shopGet !== null
        && in_array('role:vendeur', $shopGet[3], true)
        && $shopPost !== null
        && in_array('csrf', $shopPost[3], true);
});

$t->check('T03 — Normalisation des chemins', static fn () =>
    Request::normalizePath('/catalogue/') === '/catalogue'
    && Request::normalizePath('//panier//ajouter') === '/panier/ajouter'
);

// ---------------------------------------------------------------------
// 2. Composants Core
// ---------------------------------------------------------------------
$t->check('T04 — Réponse JSON valide', static function () {
    $response = Response::json(['ok' => true, 'nom' => 'Sidi Bou']);
    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

    return $response->status === 200
        && ($data['ok'] ?? false) === true
        && str_contains($response->headers['Content-Type'], 'application/json');
});

$t->check('T05 — Échappement HTML centralisé', static fn () =>
    View::e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;'
);

$t->check('T06 — Jeton CSRF de session', static function () use ($appConfig) {
    $session = new Session($appConfig['session']);
    $token = $session->csrfToken();

    return strlen($token) === 64
        && $session->validateCsrf($token)
        && !$session->validateCsrf('mauvais-jeton');
});

// ---------------------------------------------------------------------
// 3. Middlewares
// ---------------------------------------------------------------------
$t->check('T07 — CsrfMiddleware bloque un POST sans jeton (419)', static function () use ($req, $appConfig) {
    $session = new Session($appConfig['session']);
    $middleware = new CsrfMiddleware($session);
    $response = $middleware->handle($req('POST', '/panier/ajouter'), fn () => Response::html('ok'));

    return $response->status === 419;
});

$t->check('T08 — CsrfMiddleware laisse passer un POST valide', static function () use ($req, $appConfig) {
    $session = new Session($appConfig['session']);
    $token = $session->csrfToken();
    $middleware = new CsrfMiddleware($session);
    $response = $middleware->handle(
        $req('POST', '/panier/ajouter', ['_token' => $token]),
        fn () => Response::html('ok')
    );

    return $response->status === 200 && $response->body === 'ok';
});

$t->check('T09 — RoleMiddleware refuse un client sur /admin', static function () use ($req, $appConfig) {
    $session = new Session($appConfig['session']);
    $session->setUser(['id' => 2, 'role' => 'client']);

    try {
        (new RoleMiddleware($session, ['admin']))->handle($req('GET', '/admin'), fn () => Response::html('ok'));
    } catch (ForbiddenException $e) {
        return $e->status() === 403;
    }

    return false;
});

// ---------------------------------------------------------------------
// 4. Routage et rendu de bout en bout
// ---------------------------------------------------------------------
$t->check('T10 — GET / rend la page d’accueil', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/'));

    return $response->status === 200
        && str_contains($response->body, 'Coups de cœur')
        && str_contains($response->body, 'TuniShop');
});

$t->check('T11 — GET /catalogue liste les produits', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/catalogue'));

    return $response->status === 200 && str_contains($response->body, 'Vase bleu et blanc');
});

$t->check('T12 — Paramètre de route {slug} injecté dans l’action', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/produit/vase-bleu-et-blanc'));

    return $response->status === 200
        && str_contains($response->body, 'Vase bleu et blanc')
        && str_contains($response->body, '48,00 DT');
});

$t->check('T13 — GET /api/produits renvoie du JSON', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/api/produits'));
    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

    return $response->status === 200 && ($data['count'] ?? 0) >= 21;
});

$t->check('T14 — GET /sante renvoie l’état de la base', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/sante'));
    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);

    return ($data['status'] ?? null) === 'ok' && ($data['products'] ?? 0) === 24;
});

$t->check('T15 — /admin sans session redirige vers /connexion (302)', static function () use ($app, $req) {
    $response = $app->handle($req('GET', '/admin'));

    return $response->status === 302
        && ($response->headers['Location'] ?? null) === '/connexion';
});

$t->check('T16 — Route inconnue lève une 404', static function () use ($app, $req) {
    try {
        $app->handle($req('GET', '/route-inexistante'));
    } catch (\App\Exceptions\NotFoundException $e) {
        return $e->status() === 404;
    }

    return false;
});

// ---------------------------------------------------------------------
// 5. Modèles, service et règles métier
// ---------------------------------------------------------------------
$database = $app->database;

$t->check('T17 — 20 tables et 6 vues en base', static function () use ($database) {
    $tables = (int) $database->scalar(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='tunishop' AND TABLE_TYPE='BASE TABLE'"
    );
    $views = (int) $database->scalar(
        "SELECT COUNT(*) FROM information_schema.views WHERE table_schema='tunishop'"
    );

    return $tables === 20 && $views === 6;
});

$t->check('T18 — Product::catalogue() renvoie 21 produits publiés', static fn () =>
    count((new Product($database))->catalogue([], 100)) === 21
);

$t->check('T19 — Product::findBySlug()', static fn () =>
    (new Product($database))->findBySlug('vase-bleu-et-blanc') !== null
);

$t->check('T20 — RG-02/RG-03 : totaux du panier de démo (86 + 8 = 94 DT)', static function () use ($database, $app) {
    $cartService = new CartService($database, $app->session, new Cart($database));
    $totals = $cartService->totals(1);

    return (float) $totals['subtotal_ttc'] === 86.00
        && (float) $totals['shipping_fee'] === 8.00
        && (float) $totals['total_ttc'] === 94.00;
});

$t->check('T21 — RG-07 : carte de test reconnue, carte arbitraire refusée', static function () use ($database) {
    $payment = new PaymentService($database);

    return $payment->isValidTestCard('4242 4242 4242 4242')
        && !$payment->isValidTestCard('1234 5678 9012 3456');
});

$t->check('T22 — RG-08 : commande 0001 a une facture F-2026-0001', static function () use ($database) {
    $orders = new Order($database);
    $order = $orders->findByNumber('CMD-2026-0001');

    return $order !== null
        && (float) $order['total_ttc'] === 94.00
        && ($orders->invoice((int) $order['id'])['invoice_number'] ?? null) === 'F-2026-0001';
});

$t->check('T23 — Audit et opérations PDO (aucune donnée bancaire stockée)', static function () use ($database) {
    $columns = $database->fetchAll(
        "SELECT column_name FROM information_schema.columns WHERE table_schema='tunishop' AND table_name='payments'"
    );
    $names = array_column($columns, 'column_name');

    return !in_array('card_number', $names, true)
        && !in_array('cvc', $names, true)
        && !in_array('expiry', $names, true);
});

exit($t->finish());
