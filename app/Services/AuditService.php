<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/** Façade de journalisation RG-11 vers la procédure sp_log_action(). */
final class AuditService
{
    private static ?Database $database = null;
    private static ?int $userId = null;
    private static ?string $ip = null;
    private static ?string $userAgent = null;

    public static function configure(
        Database $database,
        ?int $userId,
        ?string $ip,
        ?string $userAgent,
    ): void {
        self::$database = $database;
        self::$userId = $userId;
        self::$ip = $ip;
        self::$userAgent = $userAgent;
    }

    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?string $details = null,
    ): void {
        if (self::$database === null) {
            return;
        }

        self::$database->execute(
            'CALL sp_log_action(?, ?, ?, ?)',
            [$action, $entityType, $entityId, $details]
        );
    }

    public static function currentUserId(): ?int
    {
        return self::$userId;
    }
}
