<?php

declare(strict_types=1);

namespace App\Core\ValueObject;

final readonly class Coordinates
{
    public string $lat;

    public string $lng;

    public function __construct(
        string $lat,
        string $lng,
    ) {
        $this->lat = rtrim($lat, '0');
        $this->lng = rtrim($lng, '0');
    }
}
