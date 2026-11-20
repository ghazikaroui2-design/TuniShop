<?php

declare(strict_types=1);

/**
 * TuniShop — Recette de l'intégration frontend (matière Frontend, S5).
 * Exécution : composer test:frontend  (ou php tests/frontend.php)
 * Prérequis : base `tunishop` importée, autoload Composer généré.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;

final class FrontendTest
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

$t = new FrontendTest();
$root = dirname(__DIR__);
$views = $root . '/app/Views';
$css = file_get_contents($root . '/public/assets/css/app.css') ?: '';
$js = file_get_contents($root . '/public/assets/js/app.js') ?: '';
$layout = file_get_contents($views . '/layouts/main.php') ?: '';
$checkout = file_get_contents($views . '/checkout/index.php') ?: '';
$catalog = file_get_contents($views . '/catalog/index.php') ?: '';
$product = file_get_contents($views . '/catalog/show.php') ?: '';
$cart = file_get_contents($views . '/cart/index.php') ?: '';
$dashboard = file_get_contents($views . '/dashboard/index.php') ?: '';
$account = file_get_contents($views . '/account/index.php') ?: '';

$read = static fn (string $path): string => is_file($path) ? (string) file_get_contents($path) : '';

// ---------------------------------------------------------------------
// F01 — Les 12 écrans du prototype ont une vue MVC
// ---------------------------------------------------------------------
$t->check('F01 — 12 écrans du prototype intégrés dans des vues MVC', static function () use ($views, $read): bool {
    $expected = [
        'home/index.php',
        'catalog/index.php',
        'catalog/show.php',
        'cart/index.php',
        'checkout/index.php',
        'order/confirmation.php',
        'account/index.php',
        'seller/shop.php',
        'dashboard/index.php',
        'auth/login.php',
        'auth/register.php',
        'layouts/main.php',
    ];
    foreach ($expected as $relative) {
        if (trim($read($views . '/' . $relative)) === '') {
            return false;
        }
    }

    return true;
});

// ---------------------------------------------------------------------
// F02 — Layout accessible
// ---------------------------------------------------------------------
$t->check('F02 — Layout : lang, viewport, skip link, CSS externe et JS différé', static function () use ($layout): bool {
    return str_contains($layout, '<html lang="fr">')
        && str_contains($layout, 'name="viewport"')
        && str_contains($layout, 'class="skip-link"')
        && str_contains($layout, 'id="contenu"')
        && preg_match('/<link[^>]+href="[^"]*\/assets\/css\/app\.css"/', $layout) === 1
        && preg_match('/<script[^>]+src="[^"]*\/assets\/js\/app\.js"[^>]*\bdefer\b/', $layout) === 1
        && str_contains($layout, 'data-base-url')
        && str_contains($layout, 'id="toast-stack"');
});

// ---------------------------------------------------------------------
// F03 — Aucun script inline ni attribut on* (CSP)
// ---------------------------------------------------------------------
$t->check('F03 — CSP : aucun <script> inline ni attribut on* dans les vues', static function () use ($views, $read): bool {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    foreach ($files as $file) {
        $content = $read($file);
        // On extrait la balise complete jusqu'a la fermeture du script : les
        // expressions PHP utilisees pour l'URL contiennent un '>' qui tromperait [^>]*.
        $opened = preg_match_all('/<script\b/i', $content);
        $closed = preg_match_all('#<script\b.*?</script\s*>#is', $content, $matches);
        if ($opened !== $closed) {
            return false;
        }
        foreach ($matches[0] as $tag) {
            if (!preg_match('/\bsrc\s*=/i', $tag) || !preg_match('/\bdefer\b/i', $tag)) {
                return false;
            }
        }
        if (preg_match('/\son[a-z]+\s*=\s*["\']/i', $content) === 1) {
            return false;
        }
    }

    return true;
});

// ---------------------------------------------------------------------
// F04 — En-têtes de sécurité
// ---------------------------------------------------------------------
$t->check('F04 — CSP livrée avec script-src self et sans unsafe-inline', static function (): bool {
    $headers = Response::securityHeaders();
    $csp = $headers['Content-Security-Policy'] ?? '';

    return str_contains($csp, "script-src 'self'")
        && str_contains($csp, "connect-src 'self'")
        && str_contains($csp, "form-action 'self'")
        && preg_match("/script-src[^;]*'unsafe-inline'/", $csp) === 0;
});

// ---------------------------------------------------------------------
// F05 — Hooks JavaScript externes
// ---------------------------------------------------------------------
$t->check('F05 — JS externe : 12 hooks data-* présents', static function () use ($js): bool {
    $hooks = [
        'data-base-url', 'data-nav-toggle', 'data-toast', 'data-qty-step',
        'data-filter-toggle', 'data-live-search', 'data-card-input',
        'data-expiry-input', 'data-password-meter-input', 'data-tabs',
        'data-confirm', 'table-scroll',
    ];
    foreach ($hooks as $hook) {
        if (!str_contains($js, $hook)) {
            return false;
        }
    }

    // On interdit une affectation réelle à innerHTML (vecteur XSS), pas la
    // simple mention du mot dans un commentaire de documentation.
    return str_contains($js, 'fetch(')
        && preg_match('/\binnerHTML\s*\+?=/i', $js) === 0;
});

// ---------------------------------------------------------------------
// F06 — Design system responsive et accessibilité
// ---------------------------------------------------------------------
$t->check('F06 — Design system : tokens, 640/1024 px, focus et mouvement réduit', static function () use ($css): bool {
    foreach ([':root', '--primary', '--accent', '@media (min-width: 640px)', '@media (min-width: 1024px)',
        '@media (max-width: 639px)', ':focus-visible', 'prefers-reduced-motion', '@media print',
        '.skip-link', '.table-scroll'] as $needle) {
        if (!str_contains($css, $needle)) {
            return false;
        }
    }

    return true;
});

// ---------------------------------------------------------------------
// F07 — RG-07 sur le checkout
// ---------------------------------------------------------------------
$t->check('F07 — RG-07 : carte sandbox sans name, promos et confirmation', static function () use ($checkout): bool {
    return str_contains($checkout, '4242 4242 4242 4242')
        && str_contains($checkout, 'ARTISAN5')
        && str_contains($checkout, 'BIENVENUE10')
        && str_contains($checkout, 'name="confirm_sandbox"')
        && str_contains($checkout, 'data-card-input')
        && preg_match('/name="(card_number|card_cvc|card_expiry|cvc)"/i', $checkout) === 0;
});

// ---------------------------------------------------------------------
// F08 — Catalogue et fiche produit
// ---------------------------------------------------------------------
$t->check('F08 — Catalogue : filtres, état vide, onglets ARIA et image de secours', static function () use ($catalog, $product, $root, $read): bool {
    $controller = $read($root . '/app/Controllers/CatalogController.php');

    return str_contains($catalog, 'method="get"')
        && str_contains($catalog, 'name="category"')
        && str_contains($catalog, 'name="max_price"')
        && str_contains($catalog, 'empty-state')
        && str_contains($product, 'role="tablist"')
        && substr_count($product, 'role="tab"') === 3
        && str_contains($product, 'role="tabpanel"')
        && str_contains($controller, "'exists'")
        && str_contains($controller, "'src'")
        && str_contains($controller, "'breadcrumb'");
});

// ---------------------------------------------------------------------
// F09 — Panier et tunnel de commande
// ---------------------------------------------------------------------
$t->check('F09 — Panier et commande : quantités, confirmation, TVA et étapes', static function () use ($cart, $product): bool {
    return str_contains($cart, 'class="qty"')
        && str_contains($cart, 'data-confirm')
        && str_contains($cart, 'TVA 19 %')
        && str_contains($cart, 'TOTAL TTC')
        && str_contains($cart, 'recap')
        && str_contains($product, 'form="add-to-cart-form"')
        && substr_count($product, 'data-qty-step') === 2;
});

// ---------------------------------------------------------------------
// F10 — Tableaux responsives
// ---------------------------------------------------------------------
$t->check('F10 — Back-office et compte : tableaux avec data-label', static function () use ($dashboard, $account): bool {
    return substr_count($dashboard, 'data-label=') >= 2
        && substr_count($account, 'data-label=') >= 6
        && str_contains($dashboard, 'class="table-scroll"')
        && str_contains($account, 'class="table-scroll"')
        && str_contains($dashboard, 'scope="col"')
        && str_contains($account, 'scope="col"')
        && str_contains($dashboard, 'aria-current="page"');
});

// ---------------------------------------------------------------------
// F11 — Routes du frontend
// ---------------------------------------------------------------------
$appConfig = require $root . '/config/app.php';
$dbConfig = require $root . '/config/database.php';
$routes = require $root . '/config/routes.php';
$app = new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);

$req = static function (string $method, string $path, array $post = [], array $query = []) use ($dbConfig): Request {
    return new Request($method, $path, $query, $post, [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI'    => $path,
        'REMOTE_ADDR'    => '127.0.0.1',
        'HTTP_USER_AGENT'=> 'TuniShop-Frontend-Test/1.0',
    ]);
};

$t->check('F11 — 26 routes dont /compte et /vendeur/boutique protégées', static function () use ($routes): bool {
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

    return count($routes) === 26
        && $account !== null && in_array('auth', $account[3], true)
        && $shopGet !== null && in_array('role:vendeur', $shopGet[3], true)
        && $shopPost !== null && in_array('csrf', $shopPost[3], true);
});

// ---------------------------------------------------------------------
// F12 — Rendu réel des 12 écrans
// ---------------------------------------------------------------------
$t->check('F12 — Les 12 écrans se rendent via le routeur', static function () use ($app, $req): bool {
    $screens = [
        ['/',                        null,    'client', 'Coups de cœur des artisans'],
        ['/catalogue',               null,    'client', 'Catalogue des artisans'],
        ['/produit/vase-bleu-et-blanc', null,  'client', 'Vase bleu et blanc'],
        ['/panier',                  null,    'client', 'Votre panier'],
        ['/commande',                null,    'client', 'Finaliser ma commande'],
        ['/commande/1',              null,    'admin',  'CMD-2026-0001'],
        ['/compte',                  null,    'client', 'Mes commandes'],
        ['/vendeur/boutique',        null,    'vendeur','Ma boutique'],
        ['/vendeur/produits',        null,    'vendeur','Mes produits'],
        ['/vendeur/commandes',       null,    'vendeur','Mes commandes'],
        ['/admin',                   null,    'admin',  'Administration'],
        ['/admin/vendeurs',          null,    'admin',  'Validation des vendeurs'],
    ];

    $identities = [
        'client'   => ['id' => 2,  'email' => 'aymen.benali@tunishop.tn', 'role' => 'client',   'first_name' => 'Aymen',  'last_name' => 'Ben Ali'],
        'vendeur'  => ['id' => 12, 'email' => 'fatma.bensalah@tunishop.tn','role' => 'vendeur', 'first_name' => 'Fatma',  'last_name' => 'Bensalah'],
        'admin'    => ['id' => 1,  'email' => 'ghazi.karoui@tunishop.tn',  'role' => 'admin',   'first_name' => 'Ghazi',  'last_name' => 'Karoui'],
    ];

    foreach ($screens as [$path, $post, $role, $expected]) {
        $app->session->destroy();
        if ($role !== null) {
            $app->session->setUser($identities[$role]);
        }

        $response = $app->handle($req('GET', $path, [], is_array($post) ? $post : []));
        if ($response->status !== 200 || !str_contains($response->body, $expected)) {
            return false;
        }
        if (!str_contains($response->body, '<main id="contenu"') || !str_contains($response->body, '/assets/css/app.css')) {
            return false;
        }
    }

    $app->session->destroy();

    return true;
});

exit($t->finish());
