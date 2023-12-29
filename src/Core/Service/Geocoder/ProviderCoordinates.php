<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Coordinates;

final readonly class ProviderCoordinates
{
    public function __construct(
        public GeocodingServiceProvider $serviceProvider,
        public ?Coordinates $coordinates = null,
    ) {
    }
}
