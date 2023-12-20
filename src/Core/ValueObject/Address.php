<?php

declare(strict_types=1);

namespace App\Core\ValueObject;

final readonly class Address
{
    public function __construct(
        public string $country,
        public string $city,
        public string $street,
        public string $postcode,
    ) {
    }
}
