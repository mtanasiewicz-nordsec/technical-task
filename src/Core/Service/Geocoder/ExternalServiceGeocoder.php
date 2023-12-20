<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\Cache\CoordinatesCache;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

readonly class ExternalServiceGeocoder
{
    public function __construct(
        private GeocoderRegistry $registry,
        private CoordinatesCache $coordinatesCache,
    ) {
    }

    public function geocode(Address $address, ?GeocodingServiceProvider $serviceProvider = null): ?Coordinates
    {
        if($serviceProvider !== null) {
            return $this->geocodeBySpecificServiceProvider($serviceProvider, $address);
        }

        return $this->tryAllServiceProviders($address);
    }

    private function tryAllServiceProviders(Address $address): ?Coordinates
    {
        foreach (GeocodingServiceProvider::cases() as $serviceProviderCase) {
            $coordinates = $this->geocodeBySpecificServiceProvider($serviceProviderCase, $address);
            if($coordinates !== null) {
                return $coordinates;
            }
        }

        return null;
    }

    private function geocodeBySpecificServiceProvider(GeocodingServiceProvider $serviceProvider, Address $address): ?Coordinates
    {
        $coordinates =  $this->registry
            ->getByServiceProvider($serviceProvider)
            ->geocode($address);

        if ($coordinates !== null) {
            $this->coordinatesCache->store(
                $address,
                $coordinates,
                $serviceProvider,
            );

            return $coordinates;
        }

        return null;
    }
}
