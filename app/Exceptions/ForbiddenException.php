<?php

declare(strict_types=1);

namespace App\Exceptions;

/** Accès refusé par le RBAC (HTTP 403). */
final class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Accès refusé')
    {
        parent::__construct($message, 403);
    }
}
