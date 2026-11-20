<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

/**
 * Chaîne de responsabilité : chaque middleware peut court-circuiter $next.
 */
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
