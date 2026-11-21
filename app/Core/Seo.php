<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;

/**
 * Métadonnées SEO de TuniShop (US-047 / NFR-05).
 *
 * Le JSON-LD est un « data block » : `<script type="application/ld+json">`
 * n'exécute aucun JavaScript. Il est néanmoins autorisé explicitement par un
 * nonce CSP généré par requête, donc sans `'unsafe-inline'`. Google lit ce
 * bloc directement dans le HTML, sans dépendre du JavaScript du navigateur.
 */
final class Seo
{
    public function __construct(
        private readonly string $siteUrl,
        private readonly string $nonce,
    ) {
    }

    /** URL absolue de la propriété, utilisée par canonical et Schema.org. */
    public function absolute(string $path = '/'): string
    {
        return rtrim($this->siteUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * URL canonique : aucun paramètre de filtre, aucune barre finale.
     * Le chemin normalisé est fourni par la Request via View::share().
     */
    public function canonical(?string $path = null): string
    {
        if ($path === null) {
            $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        }

        $path = rawurldecode($path);
        $path = (string) preg_replace('#/+#', '/', $path);
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        return $this->absolute($path === '' ? '/' : $path);
    }

    /** Balise canonical prête à être échappée puis affichée dans le layout. */
    public function canonicalTag(?string $path = null): string
    {
        return '<link rel="canonical" href="' . htmlspecialchars(
            $this->canonical($path),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '">';
    }

    /** @param array<string, mixed> $data */
    public function jsonLdTag(array $data): string
    {
        try {
            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return '';
        }

        // Empêche une valeur de fermer prématurément le bloc <script>.
        $json = str_replace('<', '\\u003C', $json);
        $nonce = htmlspecialchars($this->nonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<script type="application/ld+json" nonce="' . $nonce . '">' . $json . '</script>';
    }

    /** Organization + WebSite + SearchAction, partagés par toutes les pages. */
    public function globalGraph(): array
    {
        $organizationId = $this->absolute('/#organization');
        $websiteId = $this->absolute('/#website');

        return [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'Organization',
                    '@id'         => $organizationId,
                    'name'        => 'TuniShop',
                    'url'         => $this->absolute('/'),
                    'description' => 'Marketplace locale de l’artisanat tunisien à Sidi Bou Saïd.',
                    'email'       => 'bonjour@tunishop.tn',
                    'address'     => [
                        '@type'           => 'PostalAddress',
                        'addressLocality' => 'Sidi Bou Saïd',
                        'addressRegion'   => 'Tunis',
                        'addressCountry'  => 'TN',
                    ],
                    'contactPoint' => [
                        '@type'       => 'ContactPoint',
                        'contactType' => 'customer support',
                        'email'       => 'bonjour@tunishop.tn',
                        'availableLanguage' => ['fr', 'ar'],
                    ],
                ],
                [
                    '@type'      => 'WebSite',
                    '@id'        => $websiteId,
                    'url'        => $this->absolute('/'),
                    'name'       => 'TuniShop',
                    'inLanguage' => 'fr',
                    'publisher'  => ['@id' => $organizationId],
                    'potentialAction' => [
                        '@type'        => 'SearchAction',
                        'target'       => [
                            '@type'       => 'EntryPoint',
                            'urlTemplate' => $this->absolute('/catalogue?q={search_term_string}'),
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    }

    /**
     * Fil d’Ariane Schema.org.
     *
     * @param array<string, string|null> $items Libellé => URL (null pour la page courante)
     */
    public function breadcrumb(array $items): array
    {
        $elements = [[
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Accueil',
            'item'     => $this->absolute('/'),
        ]];
        $position = 2;

        foreach ($items as $label => $url) {
            $entry = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => (string) $label,
            ];
            if ($url !== null && $url !== '') {
                $entry['item'] = $this->absolute((string) $url);
            }
            $elements[] = $entry;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * Données structurées d'une fiche produit.
     *
     * @param array<string, mixed> $product Ligne de v_catalogue
     * @param array<int, array<string, mixed>> $images Images enrichies par le contrôleur
     */
    public function product(array $product, string $canonicalPath, array $images = []): array
    {
        $url = $this->absolute($canonicalPath);
        $imageUrls = [];
        foreach ($images as $image) {
            if (!empty($image['exists']) && !empty($image['src'])) {
                $imageUrls[] = $this->absolute((string) $image['src']);
            }
        }

        $data = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            '@id'         => $url . '#product',
            'name'        => (string) ($product['name'] ?? ''),
            'description' => trim(strip_tags((string) ($product['short_description'] ?? ''))),
            'url'         => $url,
            'category'    => $product['category_name'] ?? null,
            'brand'       => [
                '@type' => 'Brand',
                'name'  => (string) ($product['shop_name'] ?? 'TuniShop'),
            ],
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => $url,
                'priceCurrency' => 'TND',
                'price'         => number_format((float) ($product['price_ttc'] ?? 0), 2, '.', ''),
                'availability'  => (int) ($product['stock_qty'] ?? 0) > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name'  => (string) ($product['shop_name'] ?? 'TuniShop'),
                ],
            ],
        ];

        if ($imageUrls !== []) {
            $data['image'] = $imageUrls;
        }
        if (!empty($product['avg_rating']) && (int) ($product['review_count'] ?? 0) > 0) {
            $data['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => (string) $product['avg_rating'],
                'reviewCount' => (int) $product['review_count'],
            ];
        }

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }
}
