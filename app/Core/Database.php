<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Accès à la base via PDO (NFR-01 : requêtes préparées, pas d'émulation).
 * Transmet aussi le contexte d'audit attendu par les triggers RG-11.
 */
final class Database
{
    private ?PDO $pdo = null;
    private ?int $auditUserId = null;
    private ?string $auditIp = null;
    private ?string $auditUa = null;

    public function __construct(private readonly array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $c = $this->config;
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s',
                $c['driver'],
                $c['host'],
                $c['port'],
                $c['dbname']
            );
            $this->pdo = new PDO($dsn, $c['user'], $c['password'], $c['options']);
            $this->pdo->exec(sprintf('SET NAMES %s COLLATE %s', $c['charset'], $c['collation']));
            $this->applyAuditSession();
        }

        return $this->pdo;
    }

    /**
     * RG-11 : les triggers lisent ces variables de session pour journaliser
     * l'utilisateur, l'IP et le navigateur de l'action courante.
     */
    public function setAuditContext(?int $userId, ?string $ip, ?string $userAgent): void
    {
        $this->auditUserId = $userId;
        $this->auditIp = $ip !== null ? substr($ip, 0, 45) : null;
        $this->auditUa = $userAgent !== null ? substr($userAgent, 0, 255) : null;
        $this->applyAuditSession();
    }

    private function applyAuditSession(): void
    {
        // Le contexte peut être posé avant la première connexion : dans ce cas
        // il sera appliqué automatiquement lors de l'ouverture de la PDO.
        if ($this->pdo === null) {
            return;
        }

        $stmt = $this->pdo->prepare('SET @current_user_id = ?, @client_ip = ?, @client_ua = ?');
        $stmt->execute([$this->auditUserId, $this->auditIp, $this->auditUa]);
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo()->prepare($sql);
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /** Appel de procédure avec paramètres OUT via variables de session. */
    public function call(string $procedure, array $args, array $outVariables = []): array
    {
        $placeholders = [];
        foreach ($args as $i => $value) {
            $placeholders[] = ':' . (is_string($i) ? $i : 'p' . $i);
        }
        foreach ($outVariables as $variable) {
            $placeholders[] = '@' . ltrim($variable, '@');
        }

        $sql = 'CALL ' . $this->identifier($procedure) . '(' . implode(', ', $placeholders) . ')';
        $stmt = $this->prepare($sql);
        foreach ($args as $i => $value) {
            $name = is_string($i) ? $i : 'p' . $i;
            $stmt->bindValue(':' . $name, $value);
        }
        $stmt->execute();
        $stmt->closeCursor();

        if ($outVariables === []) {
            return [];
        }

        $selects = [];
        foreach ($outVariables as $variable) {
            $selects[] = '@' . ltrim($variable, '@') . ' AS `' . ltrim($variable, '@') . '`';
        }

        return $this->fetch('SELECT ' . implode(', ', $selects)) ?? [];
    }

    /** Whitelist des identifiants SQL (aucune interpolation libre). */
    public function identifier(string $name): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new RuntimeException('Identifiant SQL invalide : ' . $name);
        }

        return '`' . $name . '`';
    }

    public function beginTransaction(): void
    {
        $this->pdo()->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo()->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo()->inTransaction()) {
            $this->pdo()->rollBack();
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo()->inTransaction();
    }
}
