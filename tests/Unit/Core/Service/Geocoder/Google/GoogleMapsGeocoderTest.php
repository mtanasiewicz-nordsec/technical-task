<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\Google;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\Google\GoogleMapsGeocoder;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Doubles\Tool\Http\Client\PSRClientException;
use App\Tests\Doubles\Tool\Serializer\SerializerMother;
use App\Tests\Unit\UnitTest;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class GoogleMapsGeocoderTest extends UnitTest
{
    private MockObject&ClientInterface $clientMock;

    private MockObject&ResponseInterface $responseMock;

    private PSRClientException $clientException;

    private GeocoderInterface $geocoder;

    private Address $testAddress;

    private Coordinates $testCoordinates;

    private string $apiKey = 'api_key';

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->geocoder = new GoogleMapsGeocoder(
            $this->clientMock,
            SerializerMother::create(),
            $this->apiKey,
            'https://test-url/geocode',
        );

        $this->clientException = new PSRClientException('Some exception');
        $this->testAddress = new Address('country', 'city', 'street', 'postCode');
        $this->testCoordinates = new Coordinates('10.0001', '20.0002');
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenProvidedWithAddressShouldCallHttpClientWithProperRequest(): void
    {
        $this->configureCoordinatesResponse(Response::HTTP_OK, $this->testCoordinates);

        $this->clientMock
            ->expects(self::once())
            ->method('sendRequest')
            ->with(
                $this->callback(
                    function (RequestInterface $request) {
                        $this->assertProperQueryPartPassedToHttpClient(
                            [
                                'address' => $this->testAddress->street,
                                'components' => [
                                    "country:{$this->testAddress->country}",
                                    "locality:{$this->testAddress->city}",
                                    "postal_code:{$this->testAddress->postcode}",
                                ],
                                'key' => $this->apiKey,
                            ],
                            $request,
                        );

                        return true;
                    }
                )
            );

        $this->geocoder->geocode($this->testAddress);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenHttpClientReturnsSuccessfulResponseShouldReturnLatAndLng(): void
    {
        $this->configureCoordinatesResponse(Response::HTTP_OK, $this->testCoordinates);

        $result = $this->geocoder->geocode($this->testAddress);

        $this->assertSame($this->testCoordinates->lat, $result->lat);
        $this->assertSame($this->testCoordinates->lng, $result->lng);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[DataProvider('unprocessableResponse')]
    #[Test]
    public function geocodeWhenHttpClientReturnsUnprocessableResponseShouldReturnNull(string $response): void
    {
        $this->configureResponse(Response::HTTP_OK, $response);

        $result = $this->geocoder->geocode($this->testAddress);

        $this->assertNull($result);
    }

    /**
     * @return string[][]
     */
    public static function unprocessableResponse(): array
    {
        return [
            [
                <<<JSON
                {
                   "results":[]
                }
                JSON,
            ],
            [
                <<<JSON
                {
                   "results":[
                      {
                         "geometry": {
                            "location_type": "NO_ROOFTOP",
                            "location": {
                                "lat": 11,
                                "lng": 10
                            }
                         }
                      }
                   ]
                }
                JSON,
            ]
        ];
    }

    #[Test]
    public function geocodeWhenHttpClientReturnsDifferentHttpStatusCodeShouldThrowException(): void
    {
        $this->configureCoordinatesResponse(Response::HTTP_CREATED, $this->testCoordinates);

        $this->expectException(GeocodingFailedException::class);

        $this->geocoder->geocode($this->testAddress);
    }

    #[Test]
    #[DataProvider('invalidBodyResponse')]
    public function geocodeWhenHttpClientReturnsInvalidBodyShouldThrowException(string $body): void
    {
        $this->configureResponse(Response::HTTP_OK, $body);

        $this->expectException(GeocodingFailedException::class);

        $this->geocoder->geocode($this->testAddress);
    }

    /**
     * @return string[][]
     */
    public static function invalidBodyResponse(): array
    {
        return [
            [
                <<<JSON
                {
                   "results":[
                      {
                         "geometry": {
                            "location_type": "ROOFTOP",
                            "location": {
                                "latitude": 10,
                                "longitude": 11
                            }
                         }
                      }
                   ]
                }
                JSON,
            ],
            [
                <<<JSON
                {
                   "message": "something went wrong"
                }
                JSON,
            ],
        ];
    }

    #[Test]
    public function geocodeWhenHttpClientThrowsExceptionShouldThrowGeocodingFailedException(): void
    {
        $this->expectException(GeocodingFailedException::class);

        $this->clientMock
            ->method('sendRequest')
            ->willThrowException($this->clientException);

        $this->geocoder->geocode($this->testAddress);
    }

    private function configureCoordinatesResponse(int $httpStatusCode, Coordinates $coordinates): void
    {
        $lat = (float) $coordinates->lat;
        $lng = (float) $coordinates->lng;
        $this->configureResponse(
            $httpStatusCode,
            <<<JSON
            {
               "results":[
                  {
                     "geometry": {
                        "location_type": "ROOFTOP",
                        "location": {
                            "lat": $lat,
                            "lng": $lng
                        }
                     }
                  }
               ]
            }
            JSON,
        );
    }

    private function configureResponse(int $httpStatusCode, string $body): void
    {
        $this->responseMock->method('getBody')->willReturn($this->streamFor($body));
        $this->responseMock->method('getStatusCode')->willReturn($httpStatusCode);
        $this->clientMock->method('sendRequest')->willReturn($this->responseMock);
    }

    private function streamFor(string $string): StreamInterface
    {
        return Utils::streamFor($string);
    }

    private function assertProperQueryPartPassedToHttpClient(
        array $expectedResult,
        RequestInterface $request,
    ): void {
        $result = [];
        $explodedQuery = explode('&', $request->getUri()->getQuery());
        foreach ($explodedQuery as $value) {
            $explodedParam = explode('=', $value);
            if ($explodedParam[0] === 'components') {
                $result[$explodedParam[0]] = explode('%7C', $explodedParam[1]);
                sort($result[$explodedParam[0]]);
            } else {
                $result[$explodedParam[0]] = $explodedParam[1];
            }
        }
        ksort($result);

        $this->assertSame(
            $expectedResult,
            $result,
        );
    }
}
