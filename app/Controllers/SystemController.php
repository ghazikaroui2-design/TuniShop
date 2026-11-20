<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/** Route de supervision utilisée pour la recette et le monitoring. */
final class SystemController extends Controller
{
    public function health(Request $request): Response
    {
        $row = $this->database->fetch(
            'SELECT 1 AS ok, VERSION() AS version, (SELECT COUNT(*) FROM products) AS products'
        );

        return $this->json([
            'status'   => $row !== null ? 'ok' : 'error',
            'database' => $row['version'] ?? null,
            'products' => (int) ($row['products'] ?? 0),
            'env'      => 'ready',
        ]);
    }
}
