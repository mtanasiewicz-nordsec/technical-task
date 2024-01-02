<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google\Response;

final readonly class GoogleGeometry
{
    public function __construct(
        public string $location_type,
        public GoogleLocation $location,
    ) {
    }
}
