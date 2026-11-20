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
            'pageTitle' => 'Catalogue',
            'products'  => $products->catalogue($filters, 24),
            'categories' => $categories->active(),
            'filters'   => $filters,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $products = new Product($this->database);
        $product = $products->findBySlug($slug);

        if ($product === null) {
            throw new NotFoundException('Ce produit est introuvable ou n’est plus disponible.');
        }

        return $this->render('catalog/show', [
            'pageTitle' => $product['name'],
            'product'   => $product,
            'images'    => $products->images((int) $product['id']),
            'csrf'      => $this->session->csrfToken(),
        ]);
    }
}
