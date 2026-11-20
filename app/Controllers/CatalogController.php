<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Models\Product;

final class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'category'  => $request->query('category'),
            'shop'      => $request->query('shop'),
            'q'         => $request->query('q'),
            'max_price' => $request->query('max_price'),
        ];

        $products = new Product($this->database);
        $categories = new Category($this->database);

        return $this->render('catalog/index', [
            'pageTitle'     => 'Catalogue',
            'pageDescription' => 'Découvrez le catalogue TuniShop : céramique, tapis, parfums et bijoux faits main à Sidi Bou Saïd.',
            'products'      => $products->catalogue($filters, 24),
            'categories'    => $categories->active(),
            'filters'       => $filters,
            'breadcrumb'    => ['Catalogue' => null],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $products = new Product($this->database);
        $product = $products->findBySlug($slug);

        if ($product === null) {
            throw new NotFoundException('Ce produit est introuvable ou n’est plus disponible.');
        }

        $publicPath = dirname(__DIR__, 2) . '/public/';
        $images = array_map(static function (array $image) use ($publicPath): array {
            $relative = ltrim((string) $image['image_path'], '/');
            $image['src'] = '/' . $relative;
            // Les visuels de démonstration ne sont pas livrés dans le dépôt :
            // on affiche alors une illustration textuelle plutôt qu'une image cassée.
            $image['exists'] = $relative !== '' && is_file($publicPath . $relative);

            return $image;
        }, $products->images((int) $product['id']));

        return $this->render('catalog/show', [
            'pageTitle'       => $product['name'],
            'pageDescription' => $product['short_description'] ?? $product['name'] . ' — fait main à Sidi Bou Saïd.',
            'product'         => $product,
            'images'          => $images,
            'csrf'            => $this->session->csrfToken(),
            'breadcrumb'      => [
                'Catalogue'                      => '/catalogue',
                $product['category_name'] ?? ''  => '/catalogue?category=' . ($product['category_slug'] ?? ''),
                $product['name']                 => null,
            ],
        ]);
    }
}
