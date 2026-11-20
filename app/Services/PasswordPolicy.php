<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Politique de mot de passe (RG-09, OWASP A07).
 *
 * Volontairement compatible avec le compte de démonstration `Test1234!` :
 * 8 caractères minimum, au moins une minuscule, une majuscule, un chiffre
 * et un caractère spécial. Aucun mot de passe n'est stocké en clair.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH   = 8;
    public const MAX_LENGTH   = 128;

    /** @return array<string, string> Messages indexés par critère */
    public static function errors(string $password): array
    {
        $errors = [];
        $length = mb_strlen($password);

        if ($length < self::MIN_LENGTH) {
            $errors['length'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($length > self::MAX_LENGTH) {
            $errors['max'] = 'Le mot de passe ne peut pas dépasser 128 caractères.';
        }
        if (preg_match('/[a-z]/', $password) !== 1) {
            $errors['lower'] = 'Ajoutez au moins une lettre minuscule.';
        }
        if (preg_match('/[A-Z]/', $password) !== 1) {
            $errors['upper'] = 'Ajoutez au moins une lettre majuscule.';
        }
        if (preg_match('/[0-9]/', $password) !== 1) {
            $errors['digit'] = 'Ajoutez au moins un chiffre.';
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password) !== 1) {
            $errors['symbol'] = 'Ajoutez au moins un caractère spécial.';
        }

        return $errors;
    }

    public static function isValid(string $password): bool
    {
        return self::errors($password) === [];
    }

    /** Force du mot de passe pour le retour utilisateur (1 à 4). */
    public static function score(string $password): int
    {
        $score = 1;
        $score += mb_strlen($password) >= 12 ? 1 : 0;
        $score += preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1 ? 1 : 0;
        $score += preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^a-zA-Z0-9]/', $password) === 1 ? 1 : 0;

        return $score;
    }
}
