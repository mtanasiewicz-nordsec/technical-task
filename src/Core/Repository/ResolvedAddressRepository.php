<?php

declare(strict_types=1);

namespace App\Core\Repository;

use App\Core\Entity\ResolvedAddress;
use DateTime;

interface ResolvedAddressRepository
{
    public function save(ResolvedAddress $resolvedAddress): void;

    public function removeOlderThan(DateTime $cutoff): void;
}
