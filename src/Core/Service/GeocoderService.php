<?php

declare(strict_types=1);

namespace App\Core\Service;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class GeocoderService
{
    public function __construct(
        private GeocoderInterface $geocoder,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
    {
        return $this->geocoder->geocode($address);
    }
}
