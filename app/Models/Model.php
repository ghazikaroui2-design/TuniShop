<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

/**
 * Modèle de base — Active Record léger sur PDO (US-025).
 * Tout identifiant SQL est validé par whitelist avant interpolation.
 */
abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct(protected readonly Database $database)
    {
    }

    public function find(int $id): ?array
    {
        return $this->database->fetch(
            'SELECT * FROM ' . $this->tableName() . ' WHERE ' . $this->pk() . ' = ? LIMIT 1',
            [$id]
        );
    }

    public function all(int $limit = 100, int $offset = 0, string $orderBy = 'id DESC'): array
    {
        $sql = 'SELECT * FROM ' . $this->tableName() . ' ORDER BY ' . $this->safeOrder($orderBy)
            . ' LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset);

        return $this->database->fetchAll($sql);
    }

    /** @param array<string, mixed> $criteria */
    public function where(array $criteria, int $limit = 100): array
    {
        if ($criteria === []) {
            return $this->all($limit);
        }

        $conditions = [];
        $params = [];
        foreach ($criteria as $column => $value) {
            $conditions[] = $this->database->identifier((string) $column) . ' = ?';
            $params[] = $value;
        }

        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ' . implode(' AND ', $conditions)
            . ' LIMIT ' . max(1, $limit);

        return $this->database->fetchAll($sql, $params);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        if ($data === []) {
            throw new RuntimeException('Aucune donnée à insérer dans ' . $this->tableName());
        }

        $columns = [];
        $marks = [];
        $params = [];
        foreach ($data as $column => $value) {
            $columns[] = $this->database->identifier((string) $column);
            $marks[] = '?';
            $params[] = $value;
        }

        $sql = 'INSERT INTO ' . $this->tableName() . ' (' . implode(', ', $columns) . ')'
            . ' VALUES (' . implode(', ', $marks) . ')';
        $this->database->execute($sql, $params);

        return (int) $this->database->pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        $assignments = [];
        $params = [];
        foreach ($data as $column => $value) {
            $assignments[] = $this->database->identifier((string) $column) . ' = ?';
            $params[] = $value;
        }
        $params[] = $id;

        $sql = 'UPDATE ' . $this->tableName() . ' SET ' . implode(', ', $assignments)
            . ' WHERE ' . $this->pk() . ' = ?';

        return $this->database->execute($sql, $params) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->database->execute(
            'DELETE FROM ' . $this->tableName() . ' WHERE ' . $this->pk() . ' = ?',
            [$id]
        ) > 0;
    }

    public function count(): int
    {
        return (int) $this->database->scalar('SELECT COUNT(*) FROM ' . $this->tableName());
    }

    protected function tableName(): string
    {
        return $this->database->identifier($this->table);
    }

    protected function pk(): string
    {
        return $this->database->identifier($this->primaryKey);
    }

    private function safeOrder(string $orderBy): string
    {
        [$column, $direction] = array_pad(explode(' ', trim($orderBy), 2), 2, 'ASC');
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->database->identifier($column) . ' ' . $dir;
    }
}
