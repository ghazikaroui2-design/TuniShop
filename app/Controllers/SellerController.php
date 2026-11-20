<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\Shop;

/** Espace vendeur (FR-16, FR-18 à FR-21) — protégé par role:vendeur. */
final class SellerController extends Controller
{
    private function currentShopId(): int
    {
        $shop = (new Shop($this->database))->where(['owner_id' => $this->session->userId()], 1)[0] ?? null;

        return $shop === null ? 0 : (int) $shop['id'];
    }

    public function dashboard(Request $request): Response
    {
        $shopId = $this->currentShopId();
        $start = date('Y-m-01');
        $end = date('Y-m-t');

        $stats = $shopId > 0
            ? $this->database->fetch('CALL sp_get_seller_stats(?, ?, ?)', [$shopId, $start, $end])
            : null;

        return $this->render('dashboard/index', [
            'pageTitle' => 'Tableau de bord vendeur',
            'context'   => 'Vendeur',
            'stats'     => $stats ?? [],
            'rows'      => (new Product($this->database))->lowStock(),
            'columns'   => ['product_name', 'shop_name', 'stock_qty', 'low_stock_threshold'],
            'listTitle' => 'Produits en stock faible',
        ]);
    }

    public function products(Request $request): Response
    {
        $rows = (new Product($this->database))->all(50, 0, 'id DESC');

        return $this->render('dashboard/index', [
            'pageTitle' => 'Mes produits',
            'context'   => 'Vendeur',
            'stats'     => [],
            'rows'      => $rows,
            'columns'   => ['id', 'name', 'price_ttc', 'stock_qty', 'status'],
            'listTitle' => 'Catalogue',
        ]);
    }

    public function orders(Request $request): Response
    {
        return $this->render('dashboard/index', [
            'pageTitle' => 'Mes commandes',
            'context'   => 'Vendeur',
            'stats'     => [],
            'rows'      => $this->database->fetchAll('SELECT * FROM v_orders_detail LIMIT 50'),
            'columns'   => ['order_number', 'placed_at', 'first_name', 'item_count', 'total_ttc', 'status'],
            'listTitle' => 'Commandes récentes',
        ]);
    }
}
