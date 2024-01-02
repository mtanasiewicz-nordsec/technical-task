<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps\Response;

final readonly class HereMapsPosition
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }
}
