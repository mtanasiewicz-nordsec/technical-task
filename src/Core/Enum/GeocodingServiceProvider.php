<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum GeocodingServiceProvider: string
{
    case GOOGLE_MAPS = 'GOOGLE_MAPS';

    case HERE_MAPS = 'HERE_MAPS';
}
