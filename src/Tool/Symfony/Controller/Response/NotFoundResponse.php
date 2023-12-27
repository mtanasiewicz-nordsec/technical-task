<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Response;

final readonly class NotFoundResponse
{
    public function __construct(
        public string $message,
    ) {

    }
}
