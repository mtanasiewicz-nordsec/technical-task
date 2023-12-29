<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Cache;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface CoordinatesCache
{
    /**
     * @param GeocodingServiceProvider[] $serviceProviders
     */
    public function get(Address $address, array $serviceProviders = []): ?Coordinates;

    public function store(
        Address $address,
        GeocodingServiceProvider $serviceProvider,
        ?Coordinates $coordinates = null,
    ): void;

    public function clearOlderThanMinutes(int $minutes): void;
}
