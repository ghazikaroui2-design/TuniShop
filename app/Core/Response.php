<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Réponse HTTP immuable : statut, en-têtes et corps.
 *
 * NFR-01 / OWASP : les en-têtes de sécurité sont appliqués à TOUTES les
 * réponses, y compris les erreurs, avant même les en-têtes métier.
 */
final class Response
{
    public readonly string $body;
    public readonly int $status;
    public readonly array $headers;

    /** @param array<string, string> $headers */
    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = self::securityHeaders() + $headers;
    }

    /**
     * @return array<string, string>
     */
    public static function securityHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'DENY',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'geolocation=(), camera=(), microphone=(), payment=()',
            // Aucun <script> inline dans les vues ; les styles inline sont
            // autorisés pour les composants du prototype responsive.
            'Content-Security-Policy' => "default-src 'self'; base-uri 'self';"
                . " form-action 'self'; frame-ancestors 'none'; object-src 'none';"
                . " img-src 'self' data:; style-src 'self' 'unsafe-inline';"
                . " script-src 'self'; connect-src 'self'",
        ];
    }

    public static function html(string $body, int $status = 200, array $headers = []): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=utf-8'] + $headers);
    }

    public static function json(array $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new self($json, $status, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function empty(int $status = 204): self
    {
        return new self('', $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }
        echo $this->body;
    }
}
