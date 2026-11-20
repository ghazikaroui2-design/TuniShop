<?php

declare(strict_types=1);

/**
 * Configuration de la connexion PDO (US-025, NFR-01).
 * Aucun secret en dur : tout est lu depuis l'environnement.
 */
return [
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: '127.0.0.1',
    'port'      => (int) (getenv('DB_PORT') ?: 3306),
    'dbname'    => getenv('DB_NAME') ?: 'tunishop',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'user'      => getenv('DB_USER') ?: 'root',
    'password'  => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ],
];
