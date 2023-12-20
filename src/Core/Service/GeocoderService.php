<?php

declare(strict_types=1);

namespace App\Core\Service;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\Cache\CoordinatesCache;
use App\Core\Service\Geocoder\ExternalServiceGeocoder;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class GeocoderService
{
    public function __construct(
        private CoordinatesCache $coordinatesCache,
        private ExternalServiceGeocoder $externalServiceGeocoder,
    ) {
    }

    public function geocode(Address $address, ?GeocodingServiceProvider $serviceProvider = null): ?Coordinates
    {
        $cachedCoordinates = $this->coordinatesCache->get($address, $serviceProvider);

        return $cachedCoordinates ?? $this->externalServiceGeocoder->geocode($address, $serviceProvider);
    }
}
