<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\Google\Response\GoogleGeocodeResponse;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tool\Http\Client\HttpRequestBuilder;
use App\Tool\Serializer\JSON\Serializer;
use App\Tool\Serializer\SerializerFailedException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class GoogleMapsGeocoder implements GeocoderInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private Serializer $serializer,
        private string $apiKey,
        private string $endpoint,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function geocode(Address $address): ?Coordinates
    {
        $request = HttpRequestBuilder::get($this->endpoint)
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
            throw new GeocodingFailedException(
                'Geocoding failed with exception.',
                [
                    'geocoder' => self::class,
                    'exception' => $e,
                ],
                $e,
            );
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new GeocodingFailedException(
                'Geocoding request failed with unexpected status code.',
                [
                    'geocoder' => self::class,
                    'status_code' => $response->getStatusCode(),
                ]
            );
        }

        try {
            $response = $this->serializer->deserialize(
                $response->getBody()->getContents(),
                GoogleGeocodeResponse::class,
            );
        } catch (SerializerFailedException $e) {
            throw new GeocodingFailedException(
                'Decoding geocoder response failed.',
                [
                    'geocoder' => self::class,
                    'exception' => $e,
                ],
                $e,
            );
        }

        if (!$response->isProcessable()) {
            return null;
        }

        return new Coordinates(
            (string) $response->getFirstLatitude(),
            (string) $response->getFirstLongitude(),
        );
    }
}
