<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe abstraite mère de tous les contrôleurs (US-025).
 * Fournit les raccourcis de rendu et l'accès aux dépendances partagées.
 */
abstract class Controller
{
    public function __construct(
        protected readonly Database $database,
        protected readonly Session $session,
        protected readonly View $view,
    ) {
    }

    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        return Response::html($this->view->render($template, $data), $status);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect($path);
    }
}
