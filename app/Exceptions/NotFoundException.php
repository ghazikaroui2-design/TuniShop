<?php

declare(strict_types=1);

namespace App\Exceptions;

/** Ressource ou route introuvable (HTTP 404). */
final class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Ressource introuvable')
    {
        parent::__construct($message, 404);
    }
}
