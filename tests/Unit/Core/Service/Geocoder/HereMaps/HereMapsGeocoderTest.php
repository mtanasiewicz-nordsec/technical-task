<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\HereMaps;

use App\Core\Service\Geocoder\Exception\FetchingCoordinatesFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\HereMaps\HereMapsGeocoder;
use App\Core\ValueObject\Address;
use App\Tests\Doubles\Geocoder\HereMapsResponse;
use App\Tests\Doubles\Tool\Http\Client\PSRClientException;
use App\Tests\Doubles\Tool\Http\Client\StreamMother;
use App\Tests\Unit\UnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class HereMapsGeocoderTest extends UnitTest
{
    private MockObject&ClientInterface $client;

    private GeocoderInterface $geocoder;

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);
        $this->geocoder = new HereMapsGeocoder(
            $this->client,
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

        $this->assertSame('10.0001', $result->coordinates->lat);
        $this->assertSame('20.0002', $result->coordinates->lng);
    }

    /**
     * @throws Throwable
     */
    public function testUnsuccessfulResponse(): void
    {
        $this->expectException(FetchingCoordinatesFailedException::class);

        $clientResponse = $this->createMock(ResponseInterface::class);
        $clientResponse->method('getStatusCode')->willReturn(404);

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($clientResponse);

        $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
    }

    public function testInvalidBodyResponse(): void
    {
        $this->expectException(FetchingCoordinatesFailedException::class);

        $clientResponse = $this->createMock(ResponseInterface::class);
        $clientResponse->method('getStatusCode')->willReturn(200);
        $clientResponse->method('getBody')->willReturn(StreamMother::fromString('Invalid json'));

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($clientResponse);

        $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
    }

    public function testPsrClientException(): void
    {
        $this->expectException(FetchingCoordinatesFailedException::class);

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException(new PSRClientException('Some exception'));

        $this->geocoder->geocode(new Address('country', 'city', 'street', 'postCode'));
    }

    private function expectedUri(): string
    {
        return 'https://geocode.search.hereapi.com/v1/geocode?qq=country%3Dcountry;city%3Dcity;street%3Dstreet;postalCode%3DpostCode&apiKey=here_maps_api_key';
    }
}
