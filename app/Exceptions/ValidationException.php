<?php

declare(strict_types=1);

namespace App\Exceptions;

/** Données de formulaire invalides (HTTP 422). */
final class ValidationException extends HttpException
{
    /** @param array<string, string> $errors */
    public function __construct(
        public readonly array $errors,
        string $message = 'Données invalides',
    ) {
        parent::__construct($message, 422);
    }
}
