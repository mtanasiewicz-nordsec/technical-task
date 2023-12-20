<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpClientFactory;
use App\Tool\Http\Client\HttpRequestBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

final readonly class HereMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private HttpClientFactory $httpClientFactory,
        private LoggerInterface $logger,
        private string $apiKey,
    ) {
    }

    public function supports(GeocodingServiceProvider $serviceProvider): bool
    {
        return $serviceProvider === GeocodingServiceProvider::HERE_MAPS;
    }

    public function geocode(Address $address): ?Coordinates
    {
        $client = $this->httpClientFactory->create();
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
            $response = $client->sendRequest($request);
            if (!$response->isSuccessful()) {
                $this->logger->error(
                    'HereMaps Geocoding API request failed',
                    [
                        'response_content' => $response->getBody()->getContents(),
                    ],
                );

                return null;
            }

            $data = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            if (count($data['items']) === 0) {
                return null;
            }

            $firstItem = $data['items'][0];

            if ($firstItem['resultType'] !== 'houseNumber') {
                return null;
            }

            return new Coordinates(
                (string) $firstItem['position']['lat'],
                (string) $firstItem['position']['lng'],
            );
        } catch (JsonException $e) {
            if(isset($response)) {
                $this->logger->error(
                    'HereMaps Geocoding API returned invalid JSON response',
                    [
                        'response_content' => $response->getBody()->getContents(),
                        'exception' => $e,
                    ],
                );
            }

            return null;
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'HereMaps Geocoding API response error occurred: ' . $e->getMessage(),
                [
                    'exception' => $e,
                ]
            );

            return null;
        }
    }
}
