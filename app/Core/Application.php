<?php

declare(strict_types=1);

namespace App\Core;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\MiddlewareInterface;
use App\Middlewares\RoleMiddleware;
use App\Services\AuditService;

/**
 * Point d'assemblage (composition root) de l'application TuniShop.
 */
final class Application
{
    public readonly Database $database;
    public readonly Session $session;
    public readonly View $view;
    public readonly Router $router;

    /** @param array{app: array, database: array, views: string} $config */
    public function __construct(array $config, array $routes)
    {
        $this->database = new Database($config['database']);
        $this->session = new Session($config['app']['session']);
        $this->view = new View($config['views'], [
            'appName' => $config['app']['name'],
            'baseUrl' => $config['app']['base_url'],
            'appEnv'  => $config['app']['env'],
            'pricing' => $config['app']['pricing'],
            // Partagé avec le layout : état de connexion, rôle et jeton CSRF.
            'session' => $this->session,
        ]);
        $this->router = new Router($routes, $this->database, $this->session, $this->view);

        $this->bootstrapMiddlewares();
    }

    private function bootstrapMiddlewares(): void
    {
        $this->router->addMiddleware('auth', new AuthMiddleware($this->session));
        $this->router->addMiddleware('csrf', new CsrfMiddleware($this->session));

        $session = $this->session;
        $this->router->setMiddlewareFactory(
            static function (string $spec) use ($session): ?MiddlewareInterface {
                if (!str_starts_with($spec, 'role:')) {
                    return null;
                }
                $roles = array_values(array_filter(explode(',', substr($spec, 5))));

                return new RoleMiddleware($session, $roles);
            }
        );
    }

    public function handle(Request $request): Response
    {
        // RG-11 : le contexte d'audit est posé avant toute requête HTTP.
        $this->database->setAuditContext(
            $this->session->userId(),
            $request->ip(),
            $request->userAgent()
        );
        AuditService::configure(
            $this->database,
            $this->session->userId(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->router->dispatch($request);
    }
}
