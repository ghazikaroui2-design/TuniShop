<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\Product;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $products = new Product($this->database);
        $categories = new Category($this->database);

        return $this->render('home/index', [
            'pageTitle' => 'Accueil',
            'featured'  => $products->catalogue(['featured' => true], 6),
            'categories' => $categories->active(),
            'flash'     => [
                'success' => $this->session->flash('success'),
                'error'   => $this->session->flash('error'),
            ],
        ]);
    }
}
