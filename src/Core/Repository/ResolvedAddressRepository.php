<?php

declare(strict_types=1);

namespace App\Core\Repository;

use App\Core\Entity\ResolvedAddress;
use App\Core\Enum\GeocodingServiceProvider;
use DateTime;

interface ResolvedAddressRepository
{
    public function save(ResolvedAddress $resolvedAddress): void;

    public function removeOlderThan(DateTime $cutoff): void;

    /**
     * @param GeocodingServiceProvider[] $serviceProviders
     */
    public function getFirstByHashAndProviders(
        string $hash,
        array $serviceProviders
    ): ?ResolvedAddress;
}
