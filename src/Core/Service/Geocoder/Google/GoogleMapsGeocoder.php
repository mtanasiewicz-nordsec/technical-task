<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpClientFactory;
use App\Tool\Http\Client\HttpRequestBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

final readonly class GoogleMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private HttpClientFactory $httpClientFactory,
        private LoggerInterface $logger,
        private string $apiKey,
    ) {
    }

    public function supports(GeocodingServiceProvider $serviceProvider): bool
    {
        return $serviceProvider === GeocodingServiceProvider::GOOGLE_MAPS;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $client = $this->httpClientFactory->create();
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
            $response = $client->sendRequest($request);
            if (!$response->isSuccessful()) {
                $this->logger->error(
                    'Google Geocoding API request failed',
                    [
                        'response_content' => $response->getBody()->getContents(),
                    ]
                );

                return null;
            }

            $data = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            if (count($data['results']) === 0) {
                return null;
            }

            $firstResult = $data['results'][0];
            if ($firstResult['geometry']['location_type'] !== 'ROOFTOP') {
                return null;
            }

            return new Coordinates(
                (string) $firstResult['geometry']['location']['lat'],
                (string) $firstResult['geometry']['location']['lng'],
            );
        } catch (JsonException $e) {
            if(isset($response)) {
                $this->logger->error(
                    'Google Geocoding API returned invalid JSON response',
                    [
                        'response_content' => $response->getBody()->getContents(),
                        'exception' => $e,
                    ],
                );
            }

            return null;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'Google Geocoding API Client error occurred: ' . $e->getMessage(),
                [
                    'exception' => $e,
                ]
            );

            return null;
        }
    }
}
