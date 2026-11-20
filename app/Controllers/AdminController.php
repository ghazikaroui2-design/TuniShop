<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Shop;
use App\Services\ShopService;

/** Administration (FR-17, FR-22 à FR-25, RG-06) — protégé par role:admin. */
final class AdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $stats = $this->database->fetch('CALL sp_get_dashboard_stats(?, ?)', [$start, $end]);

        return $this->render('dashboard/index', [
            'pageTitle'       => 'Administration',
            'pageDescription' => 'Vue d’ensemble des ventes, commandes et boutiques de la marketplace TuniShop.',
            'breadcrumb'      => ['Administration' => null],
            'context'         => 'Administrateur',
            'boContext'       => 'admin',
            'activeMenu'      => 'dashboard',
            'stats'           => $stats ?? [],
            'chart'           => $this->weeklySales(),
            'rows'            => $this->database->fetchAll('SELECT * FROM v_low_stock LIMIT 20'),
            'columns'         => ['product_name', 'shop_name', 'stock_qty', 'low_stock_threshold'],
            'listTitle'       => 'Alertes stock',
            'pendingCount'    => (int) $this->database->scalar("SELECT COUNT(*) FROM shops WHERE status = 'en_attente'"),
        ]);
    }

    public function shops(Request $request): Response
    {
        $pending = (new Shop($this->database))->pending();

        return $this->render('dashboard/index', [
            'pageTitle'       => 'Validation des vendeurs',
            'pageDescription' => 'Validez ou refusez les nouvelles boutiques avant leur publication (RG-06).',
            'breadcrumb'      => ['Administration' => '/admin', 'Vendeurs' => null],
            'context'         => 'Administrateur',
            'boContext'       => 'admin',
            'activeMenu'      => 'vendeurs',
            'stats'           => [],
            'rows'            => $pending,
            'columns'         => ['id', 'name', 'owner_email', 'neighborhood', 'submitted_at', 'status'],
            'listTitle'       => 'Boutiques en attente',
            'pending'         => true,
            'pendingCount'    => count($pending),
            'csrf'            => $this->session->csrfToken(),
        ]);
    }

    public function validateShop(Request $request, int $id): Response
    {
        $decision = (string) $request->input('decision', '');
        $reason = $request->input('reason');
        (new ShopService($this->database, $this->session))->validate(
            $id,
            $decision,
            is_string($reason) && $reason !== '' ? $reason : null
        );
        $this->session->flash('success', 'Décision enregistrée.');

        return $this->redirect('/admin/vendeurs');
    }

    /** Série de 7 jours pour le graphique du tableau de bord. */
    private function weeklySales(): array
    {
        $since = date('Y-m-d', strtotime('-6 days'));
        $rows = $this->database->fetchAll(
            'SELECT DATE(placed_at) AS day, COUNT(*) AS total FROM orders'
            . ' WHERE placed_at >= ? GROUP BY DATE(placed_at)',
            [$since]
        );
        $byDay = [];
        foreach ($rows as $row) {
            $byDay[(string) $row['day']] = (int) $row['total'];
        }

        $labels = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
        $chart = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = date('Y-m-d', strtotime('-' . $offset . ' days'));
            $chart[] = [
                'label' => $labels[(int) date('N', strtotime($day)) - 1],
                'value' => $byDay[$day] ?? 0,
            ];
        }

        return $chart;
    }
}
