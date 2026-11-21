<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Moteur de gabarits minimaliste : layout + vue partielle.
 * L'échappement HTML est centralisé dans e() (NFR-01).
 */
final class View
{
    public function __construct(
        private readonly string $basePath,
        private array $shared = [],
    ) {
    }

    /** Ajoute des variables partagées pour le rendu courant (SEO, requête). */
    public function share(array $data): void
    {
        $this->shared = $data + $this->shared;
    }

    /** Rend une vue dans son layout. */
    public function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        // Les variables partagées (session, seo, chemin canonique) sont aussi
        // disponibles dans la vue partielle ; les données du contrôleur gagnent.
        $content = $this->renderTemplate($template, $data + $this->shared);
        if ($layout === null) {
            return $content;
        }

        return $this->renderTemplate($layout, $this->shared + $data + ['content' => $content]);
    }

    private function renderTemplate(string $template, array $data): string
    {
        $relative = str_replace('.', '/', $template);
        $path = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . $relative . '.php';

        if (!is_file($path)) {
            throw new RuntimeException('Vue introuvable : ' . $template . ' (' . $path . ')');
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;

        return (string) ob_get_clean();
    }

    /** Échappement HTML systématique des données affichées. */
    public static function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
