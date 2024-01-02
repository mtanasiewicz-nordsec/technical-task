<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Stack;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use Psr\Log\LoggerInterface;

final readonly class GeocoderStack implements GeocoderInterface
{
    /**
     * @param iterable<GeocoderInterface> $geocoders
     */
    public function __construct(
        private iterable $geocoders,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function geocode(Address $address): ?Coordinates
    {
        foreach ($this->geocoders as $geocoder) {
            try {
                $coordinates = $geocoder->geocode($address);
                if ($coordinates !== null) {
                    return $coordinates;
                }
            } catch (GeocodingFailedException $e) {
                $this->logger->error(
                    'Geocoding failed with exception',
                    [
                        'geocoder' => get_class($geocoder),
                        'exception' => $e,
                    ]
                );
            }
        }

        return null;
    }
}
