<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;

/**
 * Validation centralisée des entrées utilisateur (OWASP A03).
 *
 * Un tableau envoyé à la place d'un champ texte ne doit jamais provoquer
 * d'avertissement « Array to string conversion » ni être transmis à la BDD.
 */
final class InputSanitizer
{
    public static function string(Request $request, string $key, int $maxLength = 255): string
    {
        $value = $request->input($key, '');
        if (!is_scalar($value)) {
            return '';
        }

        $text = trim((string) $value);

        return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) : $text;
    }

    /** Comme string() mais sans trim : un mot de passe peut commencer par un espace. */
    public static function rawString(Request $request, string $key, int $maxLength = 128): string
    {
        $value = $request->input($key, '');
        if (!is_scalar($value)) {
            return '';
        }
        $text = (string) $value;

        return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) : $text;
    }

    public static function email(Request $request, string $key = 'email'): string
    {
        $email = mb_strtolower(self::string($request, $key, 190));

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '';
    }

    public static function int(
        Request $request,
        string $key,
        int $default = 0,
        int $min = PHP_INT_MIN,
        int $max = PHP_INT_MAX,
    ): int {
        $value = $request->input($key, $default);
        if (is_string($value) && preg_match('/^-?\d+$/', trim($value)) !== 1) {
            return $default;
        }
        if (!is_int($value) && !is_string($value)) {
            return $default;
        }

        return max($min, min($max, (int) $value));
    }

    /** Nettoie une chaîne destinée à être affichée ou stockée comme libellé. */
    public static function label(Request $request, string $key, int $maxLength = 150): string
    {
        $text = self::string($request, $key, $maxLength);

        return preg_replace('/[\x00-\x1F\x7F]+/u', '', $text) ?? $text;
    }
}
