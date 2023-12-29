<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\Exception\FetchingCoordinatesFailedException;
use App\Core\ValueObject\Address;

interface GeocoderInterface
{
    public function supports(GeocodingServiceProvider $serviceProvider): bool;

    /**
     * @throws FetchingCoordinatesFailedException
     */
    public function geocode(Address $address): ProviderCoordinates;
}
