<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Core\Response;
use RuntimeException;

/** Exception HTTP de base : transporte un code de statut. */
abstract class HttpException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status = 500)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    /** Rendu d'erreur neutre : aucun détail technique (NFR-01). */
    public function toResponse(): Response
    {
        $safe = htmlspecialchars($this->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $body = '<!doctype html><html lang="fr"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $this->status . ' — TuniShop</title>'
            . '<style>body{font-family:system-ui,sans-serif;background:#eef2f5;color:#16324a;'
            . 'display:grid;place-items:center;min-height:100vh;margin:0}'
            . '.box{background:#fff;padding:2.5rem;border-radius:12px;box-shadow:0 8px 26px #16324a22}'
            . 'h1{font-size:3rem;margin:0;color:#1d5d8c}a{color:#1d5d8c}</style></head><body>'
            . '<div class="box"><h1>' . $this->status . '</h1><p>' . $safe . '</p>'
            . '<a href="/">← Retour à l’accueil</a></div></body></html>';

        return Response::html($body, $this->status);
    }
}
