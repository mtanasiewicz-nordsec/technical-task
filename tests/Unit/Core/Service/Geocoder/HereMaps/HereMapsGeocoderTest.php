<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\HereMaps;

use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\HereMaps\HereMapsGeocoder;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Doubles\Geocoder\HereMapsResponse;
use App\Tests\Doubles\Tool\Http\Client\PSRClientException;
use App\Tests\Doubles\Tool\Http\Client\StreamMother;
use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpClientFactory;
use App\Tool\Http\Client\PSR\PSRClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class HereMapsGeocoderTest extends UnitTest
{
    private MockObject&ClientInterface $client;

    private GeocoderInterface $geocoder;

    private MockObject&LoggerInterface $logger;

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);
        $psrClientFactory = $this->createMock(PSRClientFactory::class);
        $psrClientFactory->method('create')->willReturn($this->client);
        $httpClientFactory = new HttpClientFactory($psrClientFactory);

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->geocoder = new HereMapsGeocoder(
            $httpClientFactory,
            $this->logger,
            'here_maps_api_key',
        );
    }

    /**
     * @throws Throwable
     */
    public function testHappyPath(): void
    {
        $clientResponse = $this->createMock(ResponseInterface::class);
        $clientResponse->method('getBody')->willReturn(HereMapsResponse::successfulResponse());
        $clientResponse->method('getStatusCode')->willReturn(200);

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(
                function (RequestInterface $request) {
                    $this->assertSame('GET', $request->getMethod());
                    $this->assertSame($this->expectedUri(), (string) $request->getUri());

                    return true;
                }
            ))
            ->willReturn($clientResponse);

        $result = $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));

        $this->assertInstanceOf(Coordinates::class, $result);
        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }

    /**
     * @throws Throwable
     */
    public function testUnsuccessfulResponse(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('HereMaps Geocoding API request failed');

        $clientResponse = $this->createMock(ResponseInterface::class);
        $clientResponse->method('getStatusCode')->willReturn(404);

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($clientResponse);

        $result = $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
        $this->assertNull($result);
    }

    public function testInvalidBodyResponse(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('HereMaps Geocoding API returned invalid JSON response');

        $clientResponse = $this->createMock(ResponseInterface::class);
        $clientResponse->method('getStatusCode')->willReturn(200);
        $clientResponse->method('getBody')->willReturn(StreamMother::fromString('Invalid json'));

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($clientResponse);

        $result = $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
        $this->assertNull($result);
    }

    public function testPsrClientException(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('HereMaps Geocoding API response error occurred: Some exception');

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException(new PSRClientException('Some exception'));

        $result = $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
        $this->assertNull($result);
    }

    private function expectedUri(): string
    {
        return 'https://geocode.search.hereapi.com/v1/geocode?qq=country%3Dcountry;city%3Dcity;street%3Dstreet;postalCode%3DpostCode&apiKey=here_maps_api_key';
    }
}
