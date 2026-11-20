<?php

declare(strict_types=1);

namespace App\Core;

use App\Middlewares\MiddlewareInterface;
use RuntimeException;

/**
 * Routeur frontal : associe méthode + URL à un contrôleur, applique le
 * pipeline de middlewares, puis injecte les paramètres nommés (PHP 8).
 */
final class Router
{
    /** @var array<string, MiddlewareInterface> */
    private array $middlewares = [];

    /** @var callable|null */
    private $middlewareFactory = null;

    public function __construct(
        private readonly array $routes,
        private readonly Database $database,
        private readonly Session $session,
        private readonly View $view,
    ) {
    }

    public function addMiddleware(string $name, MiddlewareInterface $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }

    /** Permet de résoudre les middlewares paramétrés, ex. role:vendeur. */
    public function setMiddlewareFactory(callable $factory): void
    {
        $this->middlewareFactory = $factory;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            [$method, $pattern, $handler, $specs, $name] = $route + [null, null, null, [], null];

            if ($method !== $request->method()) {
                continue;
            }

            $params = $this->match((string) $pattern, $request->path());
            if ($params === null) {
                continue;
            }

            $request->setRouteParams($params);
            $request->setRouteName($name);

            $next = fn (Request $r): Response => $this->callHandler((string) $handler, $r);

            foreach (array_reverse((array) $specs) as $spec) {
                $middleware = $this->resolveMiddleware((string) $spec);
                $next = fn (Request $r): Response => $middleware->handle($r, $next);
            }

            return $next($request);
        }

        throw new \App\Exceptions\NotFoundException('Aucune route ne correspond à ' . $request->path());
    }

    /** @return array<string, string>|null null si le chemin ne correspond pas */
    private function match(string $pattern, string $path): ?array
    {
        if ($pattern === '/') {
            return $path === '/' ? [] : null;
        }

        $parts = [];
        foreach (explode('/', trim($pattern, '/')) as $segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $m)) {
                $parts[] = '(?P<' . $m[1] . '>[^/]+)';
            } else {
                $parts[] = preg_quote($segment, '#');
            }
        }

        $regex = '#^/' . implode('/', $parts) . '/?$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $decoded = rawurldecode($value);
                // Les paramètres numériques doivent être des int pour les
                // actions typées (ex. confirmation(Request $request, int $id)).
                $params[$key] = ctype_digit($decoded) ? (int) $decoded : $decoded;
            }
        }

        return $params;
    }

    private function resolveMiddleware(string $spec): MiddlewareInterface
    {
        if (isset($this->middlewares[$spec])) {
            return $this->middlewares[$spec];
        }

        if ($this->middlewareFactory !== null) {
            $middleware = ($this->middlewareFactory)($spec);
            if ($middleware instanceof MiddlewareInterface) {
                return $middleware;
            }
        }

        throw new RuntimeException('Middleware inconnu : ' . $spec);
    }

    private function callHandler(string $handler, Request $request): Response
    {
        if (!str_contains($handler, '@')) {
            throw new RuntimeException('Handler invalide : ' . $handler);
        }

        [$class, $action] = explode('@', $handler, 2);
        if (!class_exists($class)) {
            throw new RuntimeException('Contrôleur introuvable : ' . $class);
        }

        $controller = new $class($this->database, $this->session, $this->view);
        if (!method_exists($controller, $action)) {
            throw new RuntimeException('Action introuvable : ' . $handler);
        }

        // L'union avec l'opérateur + conserve 'request' même si un paramètre
        // de route porte le même nom (les clés de gauche gagnent).
        $arguments = ['request' => $request] + $request->routeParams();

        return $controller->{$action}(...$arguments);
    }
}
