<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Encapsule la requête HTTP entrante (propriétés readonly : immuables).
 */
final class Request
{
    private array $routeParams = [];
    private ?string $routeName = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $post = [],
        public readonly array $server = [],
        public readonly ?string $rawBody = null,
    ) {
    }

    public static function fromGlobals(): self
    {
        $server = $_SERVER;
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Retire le préfixe du script (ex. /tunishop/public) pour obtenir /catalogue
        $scriptDir = str_replace('\\', '/', dirname((string) ($server['SCRIPT_NAME'] ?? '/index.php')));
        if ($scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }

        $rawBody = file_get_contents('php://input');

        return new self(
            $method,
            self::normalizePath($path),
            $_GET,
            $_POST,
            $server,
            $rawBody === false || $rawBody === '' ? null : $rawBody
        );
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        $path = (string) preg_replace('#/+#', '/', $path);

        return $path === '' ? '/' : $path;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if ($name === 'CONTENT_TYPE' || $name === 'Content-Type') {
            $key = 'CONTENT_TYPE';
        }

        return isset($this->server[$key]) ? (string) $this->server[$key] : null;
    }

    public function ip(): ?string
    {
        $ip = $this->server['REMOTE_ADDR'] ?? null;

        return $ip !== null ? (string) $ip : null;
    }

    public function userAgent(): ?string
    {
        $ua = $this->server['HTTP_USER_AGENT'] ?? null;

        return $ua !== null ? (string) $ua : null;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isJson(): bool
    {
        return str_contains(strtolower($this->header('Content-Type') ?? ''), 'application/json');
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /** @return array<string, mixed> */
    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function routeParam(string $name): mixed
    {
        return $this->routeParams[$name] ?? null;
    }

    public function setRouteName(?string $name): void
    {
        $this->routeName = $name;
    }

    public function routeName(): ?string
    {
        return $this->routeName;
    }
}
