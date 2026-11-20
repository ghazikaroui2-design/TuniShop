<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** NFR-01 : jeton CSRF obligatoire sur toute requête modifiante. */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $request->input('_token') ?? $request->header('X-CSRF-Token');

        if (!$this->session->validateCsrf(is_string($token) ? $token : null)) {
            return Response::json([
                'error' => 'Jeton CSRF invalide ou expiré.',
                'hint'  => 'Renvoyez le champ _token ou l’en-tête X-CSRF-Token.',
            ], 419);
        }

        return $next($request);
    }
}
