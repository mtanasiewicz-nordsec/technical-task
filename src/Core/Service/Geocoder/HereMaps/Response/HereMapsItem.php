<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps\Response;

final readonly class HereMapsItem
{
    public function __construct(
        public HereMapsPosition $position,
        public string $resultType = '',
    ) {
    }
}
