<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\Exception\FetchingCoordinatesFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\ProviderCoordinates;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpRequestBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

final readonly class GoogleMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    public function supports(GeocodingServiceProvider $serviceProvider): bool
    {
        return $serviceProvider === GeocodingServiceProvider::GOOGLE_MAPS;
    }

    public function geocode(Address $address): ProviderCoordinates
    {
        $request = HttpRequestBuilder::GET('https://maps.googleapis.com/maps/api/geocode/json')
            ->withQueryParams([
                'address' => $address->street,
                'components' => implode(
                    '|',
                    [
                        "country:{$address->country}",
                        "locality:{$address->city}",
                        "postal_code:{$address->postcode}",
                    ]
                ),
                'key' => $this->apiKey,
            ])
            ->build();

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new FetchingCoordinatesFailedException(
                'HttpClient thrown exception when sending GoogleMaps geocoding request',
                previous: $e,
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new FetchingCoordinatesFailedException(
                "GoogleMaps geocoding request failed with status code {$response->getStatusCode()}."
            );
        }

        try {
            $data = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw new FetchingCoordinatesFailedException(
                'Decoding GoogleMaps response failed.',
                previous: $e,
            );
        }

        if (count($data['results']) === 0) {
            return new ProviderCoordinates(GeocodingServiceProvider::GOOGLE_MAPS);
        }

        $firstResult = $data['results'][0];
        if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
            return new ProviderCoordinates(GeocodingServiceProvider::GOOGLE_MAPS);
        }

        return new ProviderCoordinates(
            GeocodingServiceProvider::GOOGLE_MAPS,
            new Coordinates(
                (string) $firstResult['geometry']['location']['lat'],
                (string) $firstResult['geometry']['location']['lng'],
            ),
        );
    }
}
