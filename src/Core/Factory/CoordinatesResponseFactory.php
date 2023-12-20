<?php

declare(strict_types=1);

namespace App\Core\Factory;

use App\Core\DTO\CoordinatesResponse;
use App\Core\ValueObject\Coordinates;

final readonly class CoordinatesResponseFactory
{
    public function createFromCoordinates(Coordinates $coordinates): CoordinatesResponse
    {
        return new CoordinatesResponse(
            $coordinates->lat,
            $coordinates->lng,
        );
    }
}
