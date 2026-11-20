<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** FR-02 / RBAC : exige une session authentifiée. */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly string $loginPath = '/connexion',
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->session->isAuthenticated()) {
            if ($request->isJson() || str_starts_with($request->path(), '/api/')) {
                return Response::json(['error' => 'Authentification requise'], 401);
            }

            $this->session->flash('error', 'Veuillez vous connecter pour continuer.');

            return Response::redirect($this->loginPath);
        }

        return $next($request);
    }
}
