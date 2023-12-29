<?php

declare(strict_types=1);

namespace App\Core\ValueObject;

final readonly class Coordinates
{
    public function __construct(
        public string $lat,
        public string $lng,
    ) {
    }
}
