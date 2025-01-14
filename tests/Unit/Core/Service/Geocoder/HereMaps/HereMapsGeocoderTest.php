<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\HereMaps;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\HereMaps\HereMapsGeocoder;
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

final class HereMapsGeocoderTest extends UnitTest
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

        $this->geocoder = new HereMapsGeocoder(
            $this->clientMock,
            SerializerMother::create(),
            $this->apiKey,
            'https://test-url/geocode',
        );

        $this->clientException = new PSRClientException('Some exception');
        $this->testAddress = new Address(
            'country',
            'city',
            'street',
            'postCode',
        );
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
                                "apiKey=$this->apiKey",
                                //qq part
                                [
                                    "city={$this->testAddress->city}",
                                    "country={$this->testAddress->country}",
                                    "postalCode={$this->testAddress->postcode}",
                                    "street={$this->testAddress->street}",
                                ]
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
    #[Test]
    #[DataProvider('unprocessableResponse')]
    public function geocodeWhenHttpClientReturnsUnprocessableShouldReturnNull(string $body): void
    {
        $this->configureResponse(Response::HTTP_OK, $body);

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
                   "items":[]
                }
                JSON,
            ],
            [
                <<<JSON
                {
                   "items":[
                      {
                         "resultType":"differentStuff",
                         "houseNumberType":"PA",
                         "position":{
                            "lat": 100,
                            "lng": 100
                         }
                      }
                   ]
                }
                JSON,
            ]
        ];
    }

    #[Test]
    public function geocodeWhenHttpClientReturnsUnsuccessfulHttpStatusShouldThrowException(): void
    {
        $this->configureCoordinatesResponse(Response::HTTP_BAD_REQUEST, $this->testCoordinates);

        $this->expectException(GeocodingFailedException::class);

        $this->geocoder->geocode($this->testAddress);
    }

    #[Test]
    public function geocodeWhenHttpClientThrowsExceptionShouldThrowException(): void
    {
        $this->clientMock
            ->method('sendRequest')
            ->willThrowException($this->clientException);

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
                   "items":[
                      {
                         "resultType":"houseNumber",
                         "houseNumberType":"PA",
                         "position":{}
                      }
                   ]
                }
            JSON,
            ],
            [
                <<<JSON
                {
                   "message": "Something went wrong."
                }
            JSON,
            ],
        ];
    }

    private function configureCoordinatesResponse(int $httpStatus, Coordinates $coordinates): void
    {
        $lat = (float) $coordinates->lat;
        $lng = (float) $coordinates->lng;
        $this->configureResponse(
            $httpStatus,
            <<<JSON
            {
               "items":[
                  {
                     "resultType": "houseNumber",
                     "houseNumberType": "PA",
                     "position":{
                        "lat": $lat,
                        "lng": $lng
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

    /**
     * @param mixed[]
     */
    private function assertProperQueryPartPassedToHttpClient(array $expected, RequestInterface $request): void
    {
        $decoded = urldecode($request->getUri()->getQuery());
        $exploded = explode('&', $decoded);
        sort($exploded);

        $this->assertCount(2, $exploded);
        $this->assertStringStartsWith('qq=', $exploded[1]);

        $exploded[1] = explode(';', str_replace('qq=', '', $exploded[1]));
        sort($exploded[1]);

        $this->assertSame($expected, $exploded);
    }
}
