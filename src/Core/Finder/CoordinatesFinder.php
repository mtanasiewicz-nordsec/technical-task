<?php

declare(strict_types=1);

namespace App\Core\Finder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface CoordinatesFinder
{
    public function get(Address $address, ?GeocodingServiceProvider $serviceProvider = null): ?Coordinates;
}
