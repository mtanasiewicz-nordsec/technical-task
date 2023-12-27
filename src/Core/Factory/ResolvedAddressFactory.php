<?php

declare(strict_types=1);

namespace App\Core\Factory;

use App\Core\Entity\ResolvedAddress;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\AddressHashGenerator;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

final readonly class ResolvedAddressFactory
{
    public function __construct(
        private AddressHashGenerator $addressHashGenerator,
    ) {
    }

    public function createFromCoordinates(
        Address $address,
        GeocodingServiceProvider $serviceProvider,
        ?Coordinates $coordinates = null,
    ): ResolvedAddress {
        return ResolvedAddress::create(
            $address->country,
            $address->city,
            $address->street,
            $address->postcode,
            $this->addressHashGenerator->generate(
                $address->country,
                $address->city,
                $address->street,
                $address->postcode,
            ),
            $coordinates?->lat,
            $coordinates?->lng,
            $serviceProvider,
        );
    }
}
