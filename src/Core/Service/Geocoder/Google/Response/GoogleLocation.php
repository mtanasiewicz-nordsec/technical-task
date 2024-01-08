<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google\Response;

final readonly class GoogleLocation
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }
}
