<?php

declare(strict_types=1);

/**
 * TuniShop — Recette de sécurité (NFR-01, RG-09, OWASP Top 10).
 * Exécution : composer test:security   (ou php tests/security.php)
 * Prérequis : base `tunishop` importée et dépendances Composer installées.
 *
 * Les tests n'effectuent aucune modification durable : le seul utilisateur
 * temporaire créé (S09) est supprimé dans un bloc finally.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Middlewares\AuthMiddleware;
use App\Models\User;
use App\Services\InputSanitizer;
use App\Services\LoginThrottle;
use App\Services\PasswordPolicy;

$root = dirname(__DIR__);
$appConfig = require $root . '/config/app.php';
$dbConfig  = require $root . '/config/database.php';
$routes    = require $root . '/config/routes.php';

final class SecurityTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function check(string $name, callable $assertion): void
    {
        try {
            $result = $assertion();
            if ($result === true) {
                $this->passed++;
                echo "PASS  $name\n";
            } else {
                $this->failed++;
                echo 'FAIL  ' . $name . ' — ' . var_export($result, true) . "\n";
            }
        } catch (Throwable $e) {
            $this->failed++;
            echo 'FAIL  ' . $name . ' — ' . get_class($e) . ' : ' . $e->getMessage() . "\n";
        }
    }

    public function finish(): int
    {
        echo "\n=== {$this->passed} PASS / {$this->failed} FAIL ===\n";

        return $this->failed === 0 ? 0 : 1;
    }
}

$t = new SecurityTest();

/** Nouvelle application (et donc nouvelle session CLI) pour chaque scénario. */
$makeApp = static fn (): Application => new Application(
    ['app' => $appConfig, 'database' => $dbConfig, 'views' => $root . '/app/Views'],
    $routes
);

/** Fabrique une requête CLI. */
$req = static function (
    string $method,
    string $path,
    array $post = [],
    array $query = [],
    array $server = [],
): Request {
    return new Request($method, $path, $query, $post, $server + [
        'REQUEST_METHOD'  => $method,
        'REQUEST_URI'     => $path,
        'REMOTE_ADDR'     => '127.0.0.1',
        'HTTP_USER_AGENT' => 'TuniShop-Security-Test/1.0',
    ]);
};

/** POST /connexion avec un jeton CSRF valide. */
$loginPost = static function (Application $app, string $email, string $password) use ($req): Request {
    return $req('POST', '/connexion', [
        '_token'   => $app->session->csrfToken(),
        'email'    => $email,
        'password' => $password,
    ]);
};

// ---------------------------------------------------------------------
// 1. En-têtes de sécurité (OWASP A05 / A02)
// ---------------------------------------------------------------------
$t->check('S01 — En-têtes de sécurité présents sur une réponse HTML', static function () {
    $headers = Response::html('<p>Sidi Bou Saïd</p>')->headers;

    return ($headers['X-Content-Type-Options'] ?? '') === 'nosniff'
        && ($headers['X-Frame-Options'] ?? '') === 'DENY'
        && ($headers['Referrer-Policy'] ?? '') === 'strict-origin-when-cross-origin'
        && str_contains($headers['Permissions-Policy'] ?? '', 'payment=()');
});

$t->check('S02 — En-têtes de sécurité présents sur une erreur 404', static function () {
    $response = (new NotFoundException('Introuvable'))->toResponse();

    return $response->status === 404
        && ($response->headers['X-Frame-Options'] ?? '') === 'DENY'
        && isset($response->headers['Content-Security-Policy']);
});

$t->check('S03 — CSP interdit les scripts inline et l’encastrement', static function () {
    $csp = Response::securityHeaders()['Content-Security-Policy'];

    return str_contains($csp, "script-src 'self'")
        && preg_match("/script-src[^;]*'unsafe-inline'/", $csp) === 0
        && str_contains($csp, "frame-ancestors 'none'")
        && str_contains($csp, "object-src 'none'");
});

$t->check('S04 — Échappement HTML centralisé contre les XSS', static fn () =>
    View::e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;'
    && View::e('<img src=x onerror="alert(1)">') === '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;'
);

// ---------------------------------------------------------------------
// 2. CSRF et contrôle d'accès (OWASP A01)
// ---------------------------------------------------------------------
$t->check('S05 — CSRF : POST sans jeton bloqué (419), jeton valide accepté', static function () use ($makeApp, $req) {
    $app = $makeApp();
    $blocked = $app->handle($req('POST', '/panier/ajouter', ['product_id' => 1]));

    $app2 = $makeApp();
    $allowed = $app2->handle($req('POST', '/panier/ajouter', [
        '_token'    => $app2->session->csrfToken(),
        'product_id' => 1,
        'quantity'   => 1,
    ]));

    return $blocked->status === 419 && $allowed->status === 302;
});

$t->check('S06 — Jeton CSRF renouvelé après régénération de session', static function () use ($appConfig) {
    $session = new Session($appConfig['session']);
    $before = $session->csrfToken();
    $session->regenerate();
    $after = $session->csrfToken();

    return $before !== $after
        && strlen($after) === 64
        && $session->validateCsrf($after)
        && !$session->validateCsrf($before);
});

$t->check('S07 — RBAC : client refusé sur /admin, admin accepté', static function () use ($makeApp, $req) {
    $clientApp = $makeApp();
    $clientApp->session->setUser(['id' => 2, 'role' => 'client']);
    $denied = false;
    try {
        $clientApp->handle($req('GET', '/admin'));
    } catch (ForbiddenException $e) {
        $denied = $e->status() === 403;
    }

    $adminApp = $makeApp();
    $adminApp->session->setUser(['id' => 1, 'role' => 'admin']);
    $allowed = $adminApp->handle($req('GET', '/admin'));

    return $denied && $allowed->status === 200;
});

$t->check('S08 — Session requise : 302 (HTML) et 401 (API)', static function () use ($makeApp, $req) {
    $app = $makeApp();
    $html = $app->handle($req('GET', '/admin'));

    $middleware = new AuthMiddleware($app->session);
    $api = $middleware->handle(
        $req('GET', '/api/commandes', [], [], ['CONTENT_TYPE' => 'application/json']),
        static fn () => Response::json(['ok' => true])
    );

    return $html->status === 302
        && ($html->headers['Location'] ?? '') === '/connexion'
        && $api->status === 401;
});

// ---------------------------------------------------------------------
// 3. Mots de passe et force brute (OWASP A07)
// ---------------------------------------------------------------------
$t->check('S09 — RG-09 : inscription hache en Argon2id', static function () use ($makeApp) {
    $email = 'sec_' . bin2hex(random_bytes(6)) . '@tunishop.tn';
    $app = $makeApp();
    $users = new User($app->database);
    $id = 0;

    try {
        $id = $users->createClient($email, 'Test1234!', 'Secu', 'Test', null);
        $row = $app->database->fetch('SELECT password_hash FROM users WHERE id = ?', [$id]);
        $hash = (string) ($row['password_hash'] ?? '');

        return str_starts_with($hash, '$argon2id$')
            && password_verify('Test1234!', $hash)
            && !str_contains($hash, 'Test1234!');
    } finally {
        if ($id > 0) {
            $app->database->execute('DELETE FROM users WHERE id = ?', [$id]);
        }
    }
});

$t->check('S10 — Politique de mot de passe : un mot fort est accepté', static fn () =>
    PasswordPolicy::isValid('Test1234!')
    && PasswordPolicy::isValid('SidiBou2026!artisanat')
    && PasswordPolicy::score('SidiBou2026!artisanat') >= 3
);

$t->check('S11 — Politique de mot de passe : un mot faible est refusé', static fn () =>
    !PasswordPolicy::isValid('password')
    && !PasswordPolicy::isValid('Ab1!')
    && !PasswordPolicy::isValid('MAJUSCULES123!')
    && !PasswordPolicy::isValid('motdepasselongsanschiffre')
);

$t->check('S12 — Verrou après 5 échecs de connexion', static function () use ($makeApp, $loginPost) {
    $app = $makeApp();
    for ($i = 0; $i < LoginThrottle::MAX_ATTEMPTS; $i++) {
        $app->handle($loginPost($app, 'personne@tunishop.tn', 'Mauvais1!'));
    }

    $throttle = new LoginThrottle($app->session);

    return $throttle->attempts() === LoginThrottle::MAX_ATTEMPTS
        && $throttle->isLocked()
        && $throttle->retryAfter() > 0;
});

$t->check('S13 — Verrou : les bons identifiants sont aussi refusés', static function () use ($makeApp, $loginPost) {
    $app = $makeApp();
    for ($i = 0; $i < LoginThrottle::MAX_ATTEMPTS; $i++) {
        $app->handle($loginPost($app, 'personne@tunishop.tn', 'Mauvais1!'));
    }

    $app->handle($loginPost($app, 'aymen.benali@tunishop.tn', 'Test1234!'));

    return $app->session->userId() === null
        && str_contains((string) $app->session->flash('error'), 'Trop de tentatives');
});

$t->check('S14 — Anti-énumération : message identique pour email inconnu ou mot de passe faux', static function () use ($makeApp, $loginPost) {
    $unknown = $makeApp();
    $unknown->handle($loginPost($unknown, 'introuvable@tunishop.tn', 'Mauvais1!'));
    $messageA = $unknown->session->flash('error');

    $wrongPassword = $makeApp();
    $wrongPassword->handle($loginPost($wrongPassword, 'aymen.benali@tunishop.tn', 'Mauvais1!'));
    $messageB = $wrongPassword->session->flash('error');

    return is_string($messageA)
        && $messageA === $messageB
        && !str_contains($messageA, 'existe')
        && !str_contains($messageA, 'introuvable');
});

// ---------------------------------------------------------------------
// 4. Injection et données sensibles (OWASP A03 / A09)
// ---------------------------------------------------------------------
$t->check('S15 — Whitelist des identifiants SQL', static function () use ($makeApp) {
    $db = $makeApp()->database;
    $valid = $db->identifier('products');
    $rejected = false;
    try {
        $db->identifier('products; DROP TABLE users');
    } catch (RuntimeException) {
        $rejected = true;
    }

    return $valid === '`products`' && $rejected;
});

$t->check('S16 — Validation des entrées : tableaux rejetés, bornes respectées', static function () use ($req) {
    $arrayInput = $req('POST', '/inscription', ['email' => ['admin@tunishop.tn']]);
    $email = $req('POST', '/inscription', ['email' => '  AYMEN@TUNISHOP.TN  ']);
    $huge = $req('POST', '/panier/ajouter', ['product_id' => '999999999999999999999']);
    $negative = $req('POST', '/panier/ajouter', ['quantity' => '-5']);

    return InputSanitizer::email($arrayInput) === ''
        && InputSanitizer::email($email) === 'aymen@tunishop.tn'
        && InputSanitizer::int($huge, 'product_id', 0, 0, 1000000) === 1000000
        && InputSanitizer::int($negative, 'quantity', 1, 1, 99) === 1;
});

$t->check('S17 — Aucun mot de passe dans les journaux d’audit', static function () use ($makeApp, $loginPost) {
    $app = $makeApp();
    $secret = 'S3cretAuditTest!' . bin2hex(random_bytes(4));
    $app->handle($loginPost($app, 'introuvable@tunishop.tn', $secret));

    $count = $app->database->scalar(
        'SELECT COUNT(*) FROM audit_logs WHERE details LIKE ?',
        ['%' . $secret . '%']
    );

    return (int) $count === 0;
});

$t->check('S18 — Configuration des cookies de session durcie', static function () use ($appConfig) {
    $session = $appConfig['session'];

    return $session['httponly'] === true
        && $session['samesite'] === 'Lax'
        && (int) $session['lifetime'] <= 7200;
});

exit($t->finish());
