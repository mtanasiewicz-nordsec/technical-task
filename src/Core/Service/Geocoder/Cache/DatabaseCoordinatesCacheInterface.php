<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Cache;

use App\Core\Factory\ResolvedAddressFactory;
use App\Core\Repository\ResolvedAddressRepository;
use App\Core\Service\AddressHashGenerator;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use DateTime;

final readonly class DatabaseCoordinatesCacheInterface implements CoordinatesCacheInterface
{
    public function __construct(
        private AddressHashGenerator $addressHashGenerator,
        private ResolvedAddressFactory $resolvedAddressFactory,
        private ResolvedAddressRepository $resolvedAddressRepository,
    ) {
    }

    public function get(Address $address): ?Coordinates
    {
        $result = $this->resolvedAddressRepository->getFirstByHash(
            $this->addressHashGenerator->generate(
                $address->country,
                $address->city,
                $address->street,
                $address->postcode,
            ),
        );

        if ($result === null || $result->getLat() === null || $result->getLng() === null) {
            return null;
        }

        return new Coordinates(
            $result->getLat(),
            $result->getLng(),
        );
    }

    public function store(
        Address $address,
        ?Coordinates $coordinates = null,
    ): void {
        $this->resolvedAddressRepository->save(
            $this->resolvedAddressFactory->createFromCoordinates(
                $address,
                $coordinates,
            )
        );
    }

    public function clearOlderThanMinutes(int $minutes): void
    {
        $now = new DateTime();
        $cutoff = $now->modify("-$minutes minutes");

        $this->resolvedAddressRepository->removeOlderThan($cutoff);
    }
}
