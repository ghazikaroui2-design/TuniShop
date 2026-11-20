<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Exceptions\ForbiddenException;

/** FR-27 : contrôle d'accès par rôle, côté serveur. */
final class RoleMiddleware implements MiddlewareInterface
{
    /** @param list<string> $allowedRoles */
    public function __construct(
        private readonly Session $session,
        private readonly array $allowedRoles,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $role = $this->session->role();

        if ($role === null || !in_array($role, $this->allowedRoles, true)) {
            throw new ForbiddenException(
                'Accès réservé au rôle : ' . implode(' ou ', $this->allowedRoles)
            );
        }

        return $next($request);
    }
}
