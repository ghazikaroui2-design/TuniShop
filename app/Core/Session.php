<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gestion sécurisée de la session PHP (NFR-01).
 * En CLI (tests), un stockage en mémoire remplace $_SESSION.
 */
final class Session
{
    private bool $started = false;
    private array $cliStore = [];

    public function __construct(private readonly array $config)
    {
    }

    private function start(): void
    {
        if ($this->started) {
            return;
        }

        if (PHP_SAPI !== 'cli') {
            session_name($this->config['name'] ?? 'tunishop_session');
            session_set_cookie_params([
                'lifetime' => (int) ($this->config['lifetime'] ?? 7200),
                'path'     => '/',
                'domain'   => '',
                'secure'   => (bool) ($this->config['secure'] ?? false),
                'httponly' => (bool) ($this->config['httponly'] ?? true),
                'samesite' => $this->config['samesite'] ?? 'Lax',
            ]);
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }

        $this->started = true;
    }

    /** @return array $_SESSION ou stockage CLI (par référence) */
    private function &store(): array
    {
        $this->start();

        if (PHP_SAPI === 'cli') {
            return $this->cliStore;
        }

        return $_SESSION;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $store = &$this->store();

        return $store[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $store = &$this->store();
        $store[$key] = $value;
    }

    public function forget(string $key): void
    {
        $store = &$this->store();
        unset($store[$key]);
    }

    /**
     * Prévention de la fixation de session (NFR-01).
     *
     * Le jeton CSRF est régénéré en même temps que l'identifiant de session :
     * un jeton créé avant l'authentification ne peut donc pas être réutilisé
     * après le changement de privilège (OWASP A01).
     */
    public function regenerate(): void
    {
        $store = &$this->store();
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        unset($store['_csrf']);
        $store['_regenerated'] = true;
    }

    public function setUser(array $user): void
    {
        $this->set('user', $user);
    }

    public function user(): ?array
    {
        $user = $this->get('user');

        return is_array($user) ? $user : null;
    }

    public function userId(): ?int
    {
        $user = $this->user();

        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public function role(): ?string
    {
        $user = $this->user();

        return isset($user['role']) ? (string) $user['role'] : null;
    }

    public function isAuthenticated(): bool
    {
        return $this->userId() !== null;
    }

    /** Message flash consommé une seule fois. */
    public function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $this->set('flash_' . $key, $message);

            return null;
        }
        $value = $this->get('flash_' . $key);
        $this->forget('flash_' . $key);

        return is_string($value) ? $value : null;
    }

    public function csrfToken(): string
    {
        $token = $this->get('_csrf');
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->set('_csrf', $token);
        }

        return $token;
    }

    public function validateCsrf(?string $token): bool
    {
        $expected = $this->csrfToken();

        return $token !== null && hash_equals($expected, $token);
    }

    public function destroy(): void
    {
        $store = &$this->store();
        $store = [];
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->started = false;
    }
}
