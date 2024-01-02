<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpRequestBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final readonly class HereMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    public function geocode(Address $address): ?Coordinates
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
            $this->logger->error(
                'HereMaps geocoding failed with exception',
                [
                    'exception' => $e,
                ]
            );

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                "HereMaps geocoding request failed with status code {$response->getStatusCode()}."
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
                'Decoding HereMaps response failed.',
                [
                    'exception' => $e,
                ]
            );

            return null;
        }

        if (count($data['items']) === 0) {
            $this->logger->info(
                'HereMaps geocoder found no coordinates',
                [
                    'address' => $address->toString(),
                ]
            );

            return null;
        }

        $firstItem = $data['items'][0];
        if ($firstItem['resultType'] !== 'houseNumber') {
            $this->logger->info(
                'HereMaps geocoder found no coordinates',
                [
                    'address' => $address->toString(),
                ]
            );

            return null;
        }

        return new Coordinates(
            (string) $firstItem['position']['lat'],
            (string) $firstItem['position']['lng'],
        );
    }
}
