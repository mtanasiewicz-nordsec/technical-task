<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface GeocoderInterface
{
    public function supports(GeocodingServiceProvider $serviceProvider): bool;

    public function geocode(Address $address): ?Coordinates;
}
