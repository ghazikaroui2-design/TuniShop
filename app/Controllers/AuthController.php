<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Services\AuditService;
use App\Services\InputSanitizer;
use App\Services\LoginThrottle;
use App\Services\PasswordPolicy;

/** Authentification, inscription et déconnexion (FR-01 à FR-04, RG-09, RG-11). */
final class AuthController extends Controller
{
    /**
     * Empreinte Argon2id fictive, vérifiée quand l'utilisateur n'existe pas,
     * afin d'égaliser le temps de réponse et d'éviter l'énumération de comptes.
     */
    private const DUMMY_HASH = '$argon2id$v=19$m=65536,t=4,p=1$ak9jS1JmNDZQcUJ4WkJ1Mg$U5KKanIp96nXauuSfYBZAjxzdxug9DvnGVdSWG1DigI';

    private const GENERIC_ERROR = 'Email ou mot de passe incorrect.';

    public function loginForm(Request $request): Response
    {
        return $this->render('auth/login', [
            'pageTitle' => 'Connexion',
            'csrf'      => $this->session->csrfToken(),
            'error'     => $this->session->flash('error'),
            'errorKey'  => 'error',
        ]);
    }

    public function login(Request $request): Response
    {
        $throttle = new LoginThrottle($this->session);

        if ($throttle->isLocked()) {
            AuditService::log('LOGIN_THROTTLED', 'users', null, 'Verrou temporaire');
            $this->session->flash('error', 'Trop de tentatives de connexion. Réessayez dans '
                . $throttle->retryAfter() . ' secondes.');

            return $this->redirect('/connexion');
        }

        $email = InputSanitizer::email($request);
        $password = InputSanitizer::rawString($request, 'password');

        $users = new User($this->database);
        $user = $users->verify($email, $password);

        if ($user === null) {
            // Même coût de calcul pour un email inconnu (anti-énumération).
            password_verify($password, self::DUMMY_HASH);
            $throttle->recordFailure();
            AuditService::log('LOGIN_FAILED', 'users', null, $email);
            $this->session->flash('error', self::GENERIC_ERROR);

            return $this->redirect('/connexion');
        }

        $throttle->reset();

        // Prévention de la fixation de session (NFR-01) + rotation du jeton CSRF.
        $this->session->regenerate();
        $this->session->setUser([
            'id'         => (int) $user['id'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
        ]);
        $users->touchLogin((int) $user['id']);

        AuditService::log('LOGIN_SUCCESS', 'users', (string) $user['id'], $user['role']);
        $this->session->flash('success', 'Bienvenue ' . $user['first_name'] . ' !');

        return $this->redirect('/');
    }

    public function registerForm(Request $request): Response
    {
        return $this->render('auth/register', [
            'pageTitle' => 'Créer un compte',
            'csrf'      => $this->session->csrfToken(),
        ]);
    }

    public function register(Request $request): Response
    {
        $email = InputSanitizer::email($request);
        $password = InputSanitizer::rawString($request, 'password');
        $firstName = InputSanitizer::label($request, 'first_name', 100);
        $lastName = InputSanitizer::label($request, 'last_name', 100);
        $phone = InputSanitizer::label($request, 'phone', 30);
        if ($phone === '') {
            $phone = null;
        }

        $errors = [];
        if ($email === '') {
            $errors['email'] = 'Adresse email invalide.';
        }
        if (PasswordPolicy::errors($password) !== []) {
            $errors['password'] = implode(' ', PasswordPolicy::errors($password));
        }
        if ($firstName === '' || $lastName === '') {
            $errors['name'] = 'Le prénom et le nom sont obligatoires.';
        }

        $users = new User($this->database);
        if ($email !== '' && $users->emailExists($email)) {
            $errors['email'] = 'Cette adresse email est déjà utilisée.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors, 'Inscription invalide.');
        }

        $id = $users->createClient($email, $password, $firstName, $lastName, $phone);

        AuditService::log('USER_REGISTERED', 'users', (string) $id, $email);
        $this->session->flash('success', 'Compte créé. Vous pouvez vous connecter.');

        return $this->redirect('/connexion');
    }

    public function logout(Request $request): Response
    {
        if ($this->session->isAuthenticated()) {
            AuditService::log('LOGOUT', 'users', (string) $this->session->userId(), 'Déconnexion');
        }
        $this->session->destroy();

        return $this->redirect('/');
    }
}
