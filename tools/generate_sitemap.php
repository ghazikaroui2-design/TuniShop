<?php

declare(strict_types=1);

/**
 * Régénère public/sitemap.xml depuis la base (US-047).
 *
 * Exécution : php tools/generate_sitemap.php
 * Le fichier produit est statique afin de rester servable à la racine sans
 * ajouter de route ni dépendre du routeur MVC. Rejouer ce script après chaque
 * publication de produit.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;
use App\Models\Product;

$root = dirname(__DIR__);
$appConfig = require $root . '/config/app.php';
$dbConfig = require $root . '/config/database.php';

$site = rtrim((string) ($appConfig['site_url'] ?? 'https://tunishop.tn'), '/');
$today = date('Y-m-d');
$esc = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

$products = (new Product(new Database($dbConfig)))->catalogue([], 1000);

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

$entries = [
    ['path' => '/',          'priority' => '1.0', 'frequency' => 'daily'],
    ['path' => '/catalogue', 'priority' => '0.9', 'frequency' => 'daily'],
];
foreach ($entries as $entry) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . $esc($site . $entry['path']) . '</loc>' . PHP_EOL;
    $xml .= '    <lastmod>' . $today . '</lastmod>' . PHP_EOL;
    $xml .= '    <changefreq>' . $entry['frequency'] . '</changefreq>' . PHP_EOL;
    $xml .= '    <priority>' . $entry['priority'] . '</priority>' . PHP_EOL;
    $xml .= '  </url>' . PHP_EOL;
}

foreach ($products as $product) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . $esc($site . '/produit/' . rawurlencode((string) $product['slug'])) . '</loc>' . PHP_EOL;
    $xml .= '    <lastmod>' . $today . '</lastmod>' . PHP_EOL;
    $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
    $xml .= '    <priority>0.7</priority>' . PHP_EOL;
    $xml .= '  </url>' . PHP_EOL;
}

$xml .= '</urlset>' . PHP_EOL;

file_put_contents($root . '/public/sitemap.xml', $xml);

echo sprintf("Sitemap regenerated: %d produit(s), %s\n", count($products), $root . '/public/sitemap.xml');
