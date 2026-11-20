<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CatalogController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\SellerController;
use App\Controllers\SystemController;

/**
 * Table de routage — [MÉTHODE, CHEMIN, Contrôleur@action, MIDDLEWARES, NOM].
 * Les segments {nom} deviennent des arguments nommés de l'action (PHP 8).
 */
return [
    // ---- Front-office public ----
    ['GET',  '/',                        HomeController::class . '@index',          [],                         'home'],
    ['GET',  '/catalogue',               CatalogController::class . '@index',      [],                         'catalog.index'],
    ['GET',  '/produit/{slug}',          CatalogController::class . '@show',       [],                         'catalog.show'],
    ['GET',  '/api/produits',            ApiController::class . '@products',      [],                         'api.products'],

    // ---- Panier (RG-01, RG-10) ----
    ['GET',  '/panier',                  CartController::class . '@index',         [],                         'cart.index'],
    ['POST', '/panier/ajouter',          CartController::class . '@add',           ['csrf'],                   'cart.add'],
    ['POST', '/panier/supprimer',        CartController::class . '@remove',        ['csrf'],                   'cart.remove'],

    // ---- Authentification (RG-09) ----
    ['GET',  '/connexion',               AuthController::class . '@loginForm',     [],                         'auth.login'],
    ['POST', '/connexion',               AuthController::class . '@login',         ['csrf'],                   'auth.login.post'],
    ['GET',  '/inscription',             AuthController::class . '@registerForm',  [],                         'auth.register'],
    ['POST', '/inscription',             AuthController::class . '@register',      ['csrf'],                   'auth.register.post'],
    ['POST', '/deconnexion',             AuthController::class . '@logout',        ['auth', 'csrf'],           'auth.logout'],

    // ---- Commande (RG-04, RG-07, RG-08) ----
    ['GET',  '/commande',                OrderController::class . '@checkout',     ['auth'],                   'order.checkout'],
    ['POST', '/commande',                OrderController::class . '@place',        ['auth', 'csrf'],           'order.place'],
    ['GET',  '/commande/{id}',           OrderController::class . '@confirmation', ['auth'],                   'order.confirmation'],
    ['GET',  '/commande/{id}/facture.pdf', OrderController::class . '@invoice',      ['auth'],                   'order.invoice'],

    // ---- Espace client (FR-07, FR-27) ----
    ['GET',  '/compte',                  AccountController::class . '@index',       ['auth'],                   'account.index'],

    // ---- Espace vendeur (RBAC) ----
    ['GET',  '/vendeur',                 SellerController::class . '@dashboard',   ['auth', 'role:vendeur'],   'seller.dashboard'],
    ['GET',  '/vendeur/boutique',        SellerController::class . '@shopForm',     ['auth', 'role:vendeur'],   'seller.shop'],
    ['POST', '/vendeur/boutique',        SellerController::class . '@shopUpdate',   ['auth', 'role:vendeur', 'csrf'], 'seller.shop.update'],
    ['GET',  '/vendeur/produits',        SellerController::class . '@products',    ['auth', 'role:vendeur'],   'seller.products'],
    ['GET',  '/vendeur/commandes',       SellerController::class . '@orders',      ['auth', 'role:vendeur'],   'seller.orders'],

    // ---- Administration (RBAC, RG-06) ----
    ['GET',  '/admin',                   AdminController::class . '@dashboard',    ['auth', 'role:admin'],     'admin.dashboard'],
    ['GET',  '/admin/vendeurs',          AdminController::class . '@shops',        ['auth', 'role:admin'],     'admin.shops'],
    ['POST', '/admin/vendeurs/{id}/valider', AdminController::class . '@validateShop',
                                                                                   ['auth', 'role:admin', 'csrf'], 'admin.shop.validate'],

    // ---- Supervision ----
    ['GET',  '/sante',                   SystemController::class . '@health',      [],                         'system.health'],
];
