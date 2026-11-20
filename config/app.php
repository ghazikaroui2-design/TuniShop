<?php

declare(strict_types=1);

/**
 * Configuration applicative — TuniShop (US-025).
 * Les valeurs peuvent être surchargées par variables d'environnement.
 */
return [
    'name'     => 'TuniShop',
    'env'      => getenv('APP_ENV') ?: 'local',
    'debug'    => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Tunis',
    'base_url' => getenv('APP_BASE_URL') ?: '/tunishop/public',
    'locale'   => 'fr_TN',

    'session' => [
        'name'     => getenv('SESSION_NAME') ?: 'tunishop_session',
        'lifetime' => 7200,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],

    // RG-10 : durée de vie d'un panier
    'cart' => [
        'lifetime_days' => 30,
    ],

    // RG-02 / RG-03
    'pricing' => [
        'vat_rate'             => 0.19,
        'shipping_fee'         => 8.00,
        'free_shipping_threshold' => 100.00,
        'currency'             => 'DT',
    ],
];
