<?php

declare(strict_types=1);

namespace App\Core\Service;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use Psr\Log\LoggerInterface;

final readonly class GeocoderService
{
    public function __construct(
        private GeocoderInterface $geocoder,
        private LoggerInterface $logger,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
    {
        try {
            return $this->geocoder->geocode($address);
        } catch (GeocodingFailedException $e) {
            $this->logger->error(
                'Unexpected exception occurred in geocoder',
                [
                    'geocoder' => self::class,
                    'exception' => $e,
                ]
            );

            return null;
        }
    }
}
