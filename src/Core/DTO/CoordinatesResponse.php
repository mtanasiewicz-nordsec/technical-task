<?php

declare(strict_types=1);

namespace App\Core\DTO;

final readonly class CoordinatesResponse
{
    public function __construct(
        public string $lat,
        public string $lng,
    ) {
    }
}
