<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Exception\GeocoderNotImplementedException;

final readonly class GeocoderRegistry
{
    /**
     * @param iterable<GeocoderInterface> $geocoders
     */
    public function __construct(
        private iterable $geocoders,
    ) {
    }

    public function getByServiceProvider(GeocodingServiceProvider $serviceProvider): GeocoderInterface
    {
        foreach ($this->geocoders as $geocoder) {
            if ($geocoder->supports($serviceProvider)) {
                return $geocoder;
            }
        }

        throw new GeocoderNotImplementedException("Geocoder {$serviceProvider->value} not implemented.");
    }
}
