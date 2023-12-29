<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface GeocoderInterface
{
    public function geocode(Address $address): ?Coordinates;
}
