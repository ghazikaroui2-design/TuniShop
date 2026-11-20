<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

/** Endpoint JSON pour les filtres dynamiques du catalogue (US-020). */
final class ApiController extends Controller
{
    public function products(Request $request): Response
    {
        $products = (new Product($this->database))->catalogue([
            'category'  => $request->query('category'),
            'shop'      => $request->query('shop'),
            'q'         => $request->query('q'),
            'max_price' => $request->query('max_price'),
        ], 50);

        return $this->json([
            'count'    => count($products),
            'products' => $products,
        ]);
    }
}
