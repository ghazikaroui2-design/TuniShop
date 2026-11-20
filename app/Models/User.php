<?php

declare(strict_types=1);

namespace App\Models;

/** Comptes utilisateurs — RG-09 : hachage Argon2id, jamais de mot de passe clair. */
final class User extends Model
{
    protected string $table = 'users';

    public function findForAuth(string $email): ?array
    {
        return $this->database->fetch(
            'SELECT u.*, r.code AS role FROM users u JOIN roles r ON r.id = u.role_id'
            . ' WHERE u.email = ? LIMIT 1',
            [$email]
        );
    }

    public function verify(string $email, string $password): ?array
    {
        $user = $this->findForAuth($email);
        if ($user === null || !(bool) $user['is_active']) {
            return null;
        }
        if (!password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    /** @return array{id:int, role:string} */
    public function createClient(string $email, string $password, string $firstName, string $lastName, ?string $phone): int
    {
        return $this->create([
            'role_id'       => 2, // client
            'email'         => mb_strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            'first_name'    => trim($firstName),
            'last_name'     => trim($lastName),
            'phone'         => $phone,
        ]);
    }

    public function touchLogin(int $id): void
    {
        $this->database->execute('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public function emailExists(string $email): bool
    {
        return $this->database->scalar(
            'SELECT COUNT(*) FROM users WHERE email = ?',
            [mb_strtolower(trim($email))]
        ) > 0;
    }
}
