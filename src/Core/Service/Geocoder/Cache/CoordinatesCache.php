<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Cache;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Factory\ResolvedAddressFactory;
use App\Core\Finder\CoordinatesFinder;
use App\Core\Repository\ResolvedAddressRepository;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use DateTime;

final readonly class CoordinatesCache
{
    public function __construct(
        private CoordinatesFinder $coordinatesFinder,
        private ResolvedAddressFactory $resolvedAddressFactory,
        private ResolvedAddressRepository $resolvedAddressRepository,
    ) {
    }

    public function get(Address $address, ?GeocodingServiceProvider $serviceProvider = null): ?Coordinates
    {
        return $this->coordinatesFinder->get(
            $address,
            $serviceProvider,
        );
    }

    public function store(Address $address, Coordinates $coordinates, GeocodingServiceProvider $serviceProvider): void
    {
        $this->resolvedAddressRepository->save(
            $this->resolvedAddressFactory->createFromCoordinates(
                $address,
                $coordinates,
                $serviceProvider,
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
