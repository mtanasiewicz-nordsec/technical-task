<?php

declare(strict_types=1);

namespace App\Core\Service;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\Cache\CoordinatesCache;
use App\Core\Service\Geocoder\Exception\FetchingCoordinatesFailedException;
use App\Core\Service\Geocoder\GeocoderRegistry;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use Psr\Log\LoggerInterface;

final readonly class GeocoderService
{
    public function __construct(
        private GeocoderRegistry $registry,
        private CoordinatesCache $coordinatesCache,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param GeocodingServiceProvider[] $serviceProviders
     */
    public function geocode(Address $address, array $serviceProviders = []): ?Coordinates
    {
        $cachedCoordinates = $this->coordinatesCache->get($address, $serviceProviders);
        if ($cachedCoordinates !== null) {
            return $cachedCoordinates;
        }

        foreach ($serviceProviders as $serviceProvider) {
            try {
                $providerCoordinates = $this->registry
                    ->getByServiceProvider($serviceProvider)
                    ->geocode($address);
            } catch (FetchingCoordinatesFailedException $e) {
                $this->logger->error(
                    "Geocoding for service provider $serviceProvider->value failed.",
                    ['exception' => $e]
                );

                continue;
            }

            $this->coordinatesCache->store(
                $address,
                $serviceProvider,
                $providerCoordinates->coordinates,
            );

            if($providerCoordinates->coordinates !== null) {
                return $providerCoordinates->coordinates;
            }
        }

        return null;
    }
}
