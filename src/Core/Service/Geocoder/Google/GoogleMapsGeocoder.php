<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpRequestBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final readonly class GoogleMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $apiKey,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
    {
        $request = HttpRequestBuilder::GET('https://maps.googleapis.com/maps/api/geocode/json')
            ->withQueryParams([
                'address' => $address->street,
                'components' => implode(
                    '|',
                    [
                        "country:$address->country",
                        "locality:$address->city",
                        "postal_code:$address->postcode",
                    ]
                ),
                'key' => $this->apiKey,
            ])
            ->build();

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'GoogleMaps geocoding failed with exception',
                [
                    'exception' => $e,
                ]
            );

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                "GoogleMaps geocoding request failed with status code {$response->getStatusCode()}."
            );

            return null;
        }

        try {
            $data = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $this->logger->error(
                'Decoding GoogleMaps response failed.',
                [
                    'exception' => $e,
                ]
            );

            return null;
        }

        if (count($data['results']) === 0) {
            $this->logger->info(
                'GoogleMaps geocoder found no coordinates',
                [
                    'address' => $address->toString(),
                ]
            );

            return null;
        }

        $firstResult = $data['results'][0];
        if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
            $this->logger->info(
                'GoogleMaps geocoder found no coordinates',
                [
                    'address' => $address->toString(),
                ]
            );

            return null;
        }

        return new Coordinates(
            (string) $firstResult['geometry']['location']['lat'],
            (string) $firstResult['geometry']['location']['lng'],
        );
    }
}
