<?php

declare(strict_types=1);

/**
 * TuniShop — Routeur frontal (Front Controller).
 * Unique point d'entrée HTTP : tout passe par ici (US-025).
 */
use App\Core\Application;
use App\Core\Response;
use App\Core\Request;
use App\Exceptions\HttpException;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

$appConfig = require $root . '/config/app.php';
$dbConfig  = require $root . '/config/database.php';
$routes    = require $root . '/config/routes.php';

date_default_timezone_set($appConfig['timezone']);

$app = new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);

try {
    $response = $app->handle(Request::fromGlobals());
} catch (HttpException $e) {
    $response = $e->toResponse();
} catch (Throwable $e) {
    // En production, aucun détail technique ne doit fuiter (NFR-01)
    $message = $appConfig['debug']
        ? htmlspecialchars((string) $e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        : 'Une erreur interne est survenue.';
    $response = Response::html('<h1>Erreur 500</h1><pre>' . $message . '</pre>', 500);
}

$response->send();
