<?php

declare(strict_types=1);

namespace App\Core\Factory;

use App\Core\Entity\ResolvedAddress;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class ResolvedAddressFactory
{
    public function createFromCoordinates(
        Address $address,
        Coordinates $coordinates,
        GeocodingServiceProvider $serviceProvider,
    ): ResolvedAddress {
        return ResolvedAddress::create(
            $serviceProvider,
            $address->country,
            $address->city,
            $address->street,
            $address->postcode,
            $coordinates->lat,
            $coordinates->lng,
        );
    }
}
