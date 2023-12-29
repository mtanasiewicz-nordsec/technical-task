<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Stack;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class GeocoderStack implements GeocoderInterface
{
    /**
     * @param iterable<GeocoderInterface> $geocoders
     */
    public function __construct(
        private iterable $geocoders,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
    {
        foreach ($this->geocoders as $geocoder) {
            $coordinates = $geocoder->geocode($address);
            if ($coordinates !== null) {
                return $coordinates;
            }
        }

        return null;
    }
}
