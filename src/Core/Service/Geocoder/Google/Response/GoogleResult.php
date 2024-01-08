<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google\Response;

final readonly class GoogleResult
{
    public function __construct(
        public GoogleGeometry $geometry,
    ) {
    }
}
