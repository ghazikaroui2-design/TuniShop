<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Services\AuditService;

/** Authentification, inscription et déconnexion (FR-01 à FR-04, RG-09, RG-11). */
final class AuthController extends Controller
{
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
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        $users = new User($this->database);
        $user = $users->verify($email, $password);

        if ($user === null) {
            AuditService::log('LOGIN_FAILED', 'users', null, $email);
            $this->session->flash('error', 'Email ou mot de passe incorrect.');

            return $this->redirect('/connexion');
        }

        // Prévention de la fixation de session (NFR-01)
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
        $email = mb_strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $firstName = trim((string) $request->input('first_name', ''));
        $lastName = trim((string) $request->input('last_name', ''));

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }
        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($firstName === '' || $lastName === '') {
            $errors['name'] = 'Le prénom et le nom sont obligatoires.';
        }

        $users = new User($this->database);
        if ($users->emailExists($email)) {
            $errors['email'] = 'Cette adresse email est déjà utilisée.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors, 'Inscription invalide.');
        }

        $id = $users->createClient($email, $password, $firstName, $lastName,
            is_string($request->input('phone')) ? $request->input('phone') : null);

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
