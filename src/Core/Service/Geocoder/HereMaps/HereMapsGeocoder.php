<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps;

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

final readonly class HereMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    public function supports(GeocodingServiceProvider $serviceProvider): bool
    {
        return $serviceProvider === GeocodingServiceProvider::HERE_MAPS;
    }

    public function geocode(Address $address): ProviderCoordinates
    {
        $request = HttpRequestBuilder::GET('https://geocode.search.hereapi.com/v1/geocode')
            ->withQueryParams([
                'qq' => implode(
                    ';',
                    [
                        "country={$address->country}",
                        "city={$address->city}",
                        "street={$address->street}",
                        "postalCode={$address->postcode}"
                    ],
                ),
                'apiKey' => $this->apiKey,
            ])
            ->build();

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new FetchingCoordinatesFailedException(
                'HttpClient thrown exception when sending HereMaps geocoding request',
                previous: $e,
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new FetchingCoordinatesFailedException(
                "HereMaps geocoding request failed with status code {$response->getStatusCode()}."
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
                'Decoding HereMaps response failed.',
                previous: $e,
            );
        }

        if (count($data['items']) === 0) {
            return new ProviderCoordinates(GeocodingServiceProvider::HERE_MAPS);
        }

        $firstItem = $data['items'][0];
        if ($firstItem['resultType'] !== 'houseNumber') {
            return new ProviderCoordinates(GeocodingServiceProvider::HERE_MAPS);
        }

        return new ProviderCoordinates(
            GeocodingServiceProvider::HERE_MAPS,
            new Coordinates(
                (string) $firstItem['position']['lat'],
                (string) $firstItem['position']['lng'],
            )
        );
    }
}
