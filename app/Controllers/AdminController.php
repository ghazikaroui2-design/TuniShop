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
            'pageTitle' => 'Administration',
            'context'   => 'Administrateur',
            'stats'     => $stats ?? [],
            'rows'      => $this->database->fetchAll('SELECT * FROM v_low_stock LIMIT 20'),
            'columns'   => ['product_name', 'shop_name', 'stock_qty', 'low_stock_threshold'],
            'listTitle' => 'Alertes stock',
        ]);
    }

    public function shops(Request $request): Response
    {
        return $this->render('dashboard/index', [
            'pageTitle' => 'Validation des vendeurs',
            'context'   => 'Administrateur',
            'stats'     => [],
            'rows'      => (new Shop($this->database))->pending(),
            'columns'   => ['id', 'name', 'owner_email', 'neighborhood', 'submitted_at', 'status'],
            'listTitle' => 'Boutiques en attente',
            'pending'   => true,
            'csrf'      => $this->session->csrfToken(),
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
}
