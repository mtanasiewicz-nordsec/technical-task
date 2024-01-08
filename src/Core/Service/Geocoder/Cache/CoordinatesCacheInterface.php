<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Cache;

use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;

interface CoordinatesCacheInterface
{
    public function get(Address $address): ?Coordinates;

    public function store(
        Address $address,
        ?Coordinates $coordinates = null,
    ): void;

    public function clearOlderThanMinutes(int $minutes): void;
}
