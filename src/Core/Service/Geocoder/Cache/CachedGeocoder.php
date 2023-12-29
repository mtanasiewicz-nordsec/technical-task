<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Cache;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class CachedGeocoder implements GeocoderInterface
{
    public function __construct(
        public CoordinatesCache $coordinatesCache,
        public GeocoderInterface $geocoder,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
    {
        $cachedCoordinates = $this->coordinatesCache->get($address);
        if ($cachedCoordinates !== null) {
            return $cachedCoordinates;
        }

        $coordinates = $this->geocoder->geocode($address);
        $this->coordinatesCache->store(
            $address,
            $coordinates,
        );

        return $coordinates;
    }
}
