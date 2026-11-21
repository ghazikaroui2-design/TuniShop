<?php

declare(strict_types=1);

/**
 * TuniShop — Recette SEO technique (US-047 / NFR-05, lot P0).
 * Exécution : composer test:seo  (ou php tests/seo.php)
 * Prérequis : base `tunishop` importée, autoload Composer généré.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;

final class SeoTest
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

$t = new SeoTest();
$root = dirname(__DIR__);
$appConfig = require $root . '/config/app.php';
$dbConfig = require $root . '/config/database.php';
$routes = require $root . '/config/routes.php';

$app = new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);

$req = static fn (string $path): Request => new Request('GET', $path, [], [], [
    'REQUEST_METHOD'  => 'GET',
    'REQUEST_URI'     => $path,
    'REMOTE_ADDR'     => '127.0.0.1',
    'HTTP_USER_AGENT' => 'TuniShop-SEO-Test/1.0',
]);

$productHtml = $app->handle($req('/produit/vase-bleu-et-blanc'))->body;
$catalogHtml = $app->handle($req('/catalogue'))->body;
$csp = Response::securityHeaders()['Content-Security-Policy'] ?? '';
preg_match("/'nonce-([a-f0-9]+)'/", $csp, $nonceMatch);
$nonce = $nonceMatch[1] ?? '';

// ---------------------------------------------------------------------
// SEO-01 — URL publique absolue configurée
// ---------------------------------------------------------------------
$t->check('SEO-01 — site_url absolu configuré pour canonical et sitemap', static function () use ($appConfig): bool {
    $site = (string) ($appConfig['site_url'] ?? '');

    return filter_var($site, FILTER_VALIDATE_URL) !== false
        && str_starts_with($site, 'https://');
});

// ---------------------------------------------------------------------
// SEO-02 — Canonical
// ---------------------------------------------------------------------
$t->check('SEO-02 — canonical unique, absolu et sans paramètre de filtre', static function () use ($productHtml, $catalogHtml): bool {
    $productCanonical = '<link rel="canonical" href="https://tunishop.tn/produit/vase-bleu-et-blanc">';
    $catalogCanonical = '<link rel="canonical" href="https://tunishop.tn/catalogue">';

    return substr_count($productHtml, 'rel="canonical"') === 1
        && str_contains($productHtml, $productCanonical)
        && str_contains($catalogHtml, $catalogCanonical)
        && !str_contains($catalogHtml, 'rel="canonical" href="https://tunishop.tn/catalogue?');
});

// ---------------------------------------------------------------------
// SEO-03 — robots.txt
// ---------------------------------------------------------------------
$t->check('SEO-03 — robots.txt indexe le public et exclut les espaces privés', static function () use ($root): bool {
    $path = $root . '/public/robots.txt';
    if (!is_file($path)) {
        return false;
    }
    $content = (string) file_get_contents($path);
    foreach (['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /vendeur',
        'Disallow: /compte', 'Disallow: /commande', 'Disallow: /panier',
        'Sitemap: https://tunishop.tn/sitemap.xml'] as $needle) {
        if (!str_contains($content, $needle)) {
            return false;
        }
    }

    return true;
});

// ---------------------------------------------------------------------
// SEO-04 — sitemap.xml
// ---------------------------------------------------------------------
$t->check('SEO-04 — sitemap.xml valide avec accueil, catalogue et produits', static function () use ($root): bool {
    $path = $root . '/public/sitemap.xml';
    if (!is_file($path)) {
        return false;
    }
    $xml = simplexml_load_file($path);
    if ($xml === false) {
        return false;
    }
    $locs = [];
    foreach ($xml->url as $url) {
        $locs[] = (string) $url->loc;
    }

    return count($locs) >= 20
        && in_array('https://tunishop.tn/', $locs, true)
        && in_array('https://tunishop.tn/catalogue', $locs, true)
        && in_array('https://tunishop.tn/produit/vase-bleu-et-blanc', $locs, true);
});

// ---------------------------------------------------------------------
// SEO-05 — nonce CSP (pas de unsafe-inline)
// ---------------------------------------------------------------------
$t->check('SEO-05 — CSP stricte avec nonce et sans unsafe-inline', static function () use ($csp, $nonce): bool {
    return str_contains($csp, "script-src 'self'")
        && preg_match("/script-src[^;]*'unsafe-inline'/", $csp) === 0
        && $nonce !== ''
        && str_contains($csp, "'nonce-" . $nonce . "'");
});

// ---------------------------------------------------------------------
// SEO-06 — Blocs JSON-LD avec nonce dans le HTML rendu
// ---------------------------------------------------------------------
$t->check('SEO-06 — JSON-LD rendu côté serveur et autorisé par le nonce CSP', static function () use ($productHtml, $nonce): bool {
    $blocks = [];
    if (preg_match_all('#<script[^>]*type="application/ld\+json"[^>]*>.*?</script>#is', $productHtml, $matches) === false) {
        return false;
    }
    $blocks = $matches[0];
    if (count($blocks) < 2) {
        return false;
    }
    foreach ($blocks as $block) {
        if (!str_contains($block, 'nonce="' . $nonce . '"')) {
            return false;
        }
    }

    return true;
});

// ---------------------------------------------------------------------
// SEO-07 — Organization / WebSite / SearchAction
// ---------------------------------------------------------------------
$t->check('SEO-07 — @graph Organization + WebSite + SearchAction', static function () use ($appConfig, $nonce): bool {
    $seo = new Seo((string) $appConfig['site_url'], $nonce);
    $graph = $seo->globalGraph();
    $types = array_column($graph['@graph'], '@type');
    $site = $graph['@graph'][1] ?? [];

    return in_array('Organization', $types, true)
        && in_array('WebSite', $types, true)
        && ($site['potentialAction']['@type'] ?? '') === 'SearchAction'
        && str_contains((string) ($site['potentialAction']['target']['urlTemplate'] ?? ''), 'q={search_term_string}');
});

// ---------------------------------------------------------------------
// SEO-08 — BreadcrumbList
// ---------------------------------------------------------------------
$t->check('SEO-08 — BreadcrumbList sur la fiche produit', static function () use ($productHtml, $appConfig, $nonce): bool {
    $seo = new Seo((string) $appConfig['site_url'], $nonce);
    $breadcrumb = $seo->breadcrumb(['Catalogue' => '/catalogue', 'Vase' => null]);
    $items = $breadcrumb['itemListElement'] ?? [];

    return str_contains($productHtml, '"@type":"BreadcrumbList"')
        && $breadcrumb['@type'] === 'BreadcrumbList'
        && count($items) === 3
        && $items[0]['position'] === 1
        && $items[2]['position'] === 3
        && !isset($items[2]['item']);
});

// ---------------------------------------------------------------------
// SEO-09 — Product / Offer TND
// ---------------------------------------------------------------------
$t->check('SEO-09 — Product avec offre TND, prix et disponibilité', static function () use ($appConfig, $nonce): bool {
    $seo = new Seo((string) $appConfig['site_url'], $nonce);
    $product = $seo->product([
        'name' => 'Vase bleu et blanc',
        'short_description' => 'Vase tourné et peint main',
        'price_ttc' => 48,
        'stock_qty' => 12,
        'category_name' => 'Céramique',
        'shop_name' => 'Atelier Bleu',
    ], '/produit/vase-bleu-et-blanc');
    $offer = $product['offers'] ?? [];

    return $product['@type'] === 'Product'
        && ($offer['priceCurrency'] ?? '') === 'TND'
        && ($offer['price'] ?? '') === '48.00'
        && str_ends_with((string) ($offer['availability'] ?? ''), 'InStock')
        && $product['url'] === 'https://tunishop.tn/produit/vase-bleu-et-blanc';
});

// ---------------------------------------------------------------------
// SEO-10 — X-Robots-Tag
// ---------------------------------------------------------------------
$t->check('SEO-10 — X-Robots-Tag noindex sur le privé, index sur le public', static function () use ($app, $req): bool {
    $identities = [
        'client' => ['id' => 2, 'email' => 'aymen.benali@tunishop.tn', 'role' => 'client',
            'first_name' => 'Aymen', 'last_name' => 'Ben Ali'],
        'admin'  => ['id' => 1, 'email' => 'ghazi.karoui@tunishop.tn', 'role' => 'admin',
            'first_name' => 'Ghazi', 'last_name' => 'Karoui'],
    ];

    $app->session->destroy();
    $app->session->setUser($identities['client']);
    $private = $app->handle($req('/compte'));
    $app->session->destroy();
    $app->session->setUser($identities['admin']);
    $admin = $app->handle($req('/admin'));
    $app->session->destroy();
    $public = $app->handle($req('/catalogue'));

    return ($private->headers['X-Robots-Tag'] ?? '') === 'noindex, nofollow'
        && ($admin->headers['X-Robots-Tag'] ?? '') === 'noindex, nofollow'
        && ($public->headers['X-Robots-Tag'] ?? '') === 'index, follow';
});

// ---------------------------------------------------------------------
// SEO-11 — Pas d'injection de fermeture de script
// ---------------------------------------------------------------------
$t->check('SEO-11 — JSON-LD neutralise toute balise dans les données', static function () use ($appConfig, $nonce): bool {
    $seo = new Seo((string) $appConfig['site_url'], $nonce);
    $tag = $seo->jsonLdTag(['name' => '</script><script>alert(1)</script>']);

    return !str_contains($tag, '</script><script>')
        && str_contains($tag, '\\u003C/script>')
        && substr_count($tag, '</script>') === 1;
});

// ---------------------------------------------------------------------
// SEO-12 — Aucun script exécutable inline dans le rendu
// ---------------------------------------------------------------------
$t->check('SEO-12 — Aucun script inline exécutable, seuls des data blocks nonce', static function () use ($productHtml): bool {
    if (preg_match_all('#<script\b[^>]*>.*?</script\s*>#is', $productHtml, $matches) === false) {
        return false;
    }
    foreach ($matches[0] as $tag) {
        $isDataBlock = str_contains($tag, 'type="application/ld+json"')
            && preg_match('/\bnonce="[a-f0-9]+"/', $tag) === 1;
        $isExternal = preg_match('/\bsrc=/', $tag) === 1 && preg_match('/\bdefer\b/', $tag) === 1;
        if (!$isDataBlock && !$isExternal) {
            return false;
        }
    }

    return substr_count($productHtml, 'type="application/ld+json"') >= 2
        && substr_count($productHtml, '<h1') === 1;
});

exit($t->finish());
