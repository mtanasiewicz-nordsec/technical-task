<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Response;

final readonly class BadRequestResponse
{
    /**
     * @param array<string, string[]> $messages
     */
    public function __construct(
        /** @var array<string, string[]> $messages */
        public array $messages,
    ) {
    }
}
