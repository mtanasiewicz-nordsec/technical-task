<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Exception\GeocodingFailedException;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface GeocoderInterface
{
    /**
     * @throws GeocodingFailedException
     */
    public function geocode(Address $address): ?Coordinates;
}
