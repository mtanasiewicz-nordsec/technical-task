<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Service;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Finder\CoordinatesFinder;
use App\Core\Finder\DoctrineCoordinatesFinder;
use App\Core\Service\GeocoderService;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Doubles\Geocoder\HereMapsResponse;
use App\Tests\Integration\IntegrationTest;
use App\Tool\Http\Client\PSR\GuzzlePSRClientFactory;
use App\Tool\Http\Client\PSR\PSRClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class GeocoderServiceTest extends IntegrationTest
{
    private MockObject&ClientInterface $client;

    private MockObject&CoordinatesFinder $coordinatesFinder;

    private GeocoderService $geocoderService;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->coordinatesFinder = $this->createMock(CoordinatesFinder::class);
        self::getContainer()->set(DoctrineCoordinatesFinder::class, $this->coordinatesFinder);

        $this->client = $this->createMock(ClientInterface::class);
        $psrClientFactory = $this->createMock(PSRClientFactory::class);
        $psrClientFactory->method('create')->willReturn($this->client);
        self::getContainer()->set(GuzzlePSRClientFactory::class, $psrClientFactory);

        $this->geocoderService = self::getContainer()->get(GeocoderService::class);
    }

    /**
     * @throws Throwable
     */
    public function testItTriesToResolveAllServices(): void
    {
        $this->coordinatesFinder->method('get')->willReturn(null);
        $unsuccessfulResponse = $this->createMock(ResponseInterface::class);
        $unsuccessfulResponse->method('getStatusCode')->willReturn(404);
        $this->client
            ->expects($this->exactly(count(GeocodingServiceProvider::cases())))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                ...array_map(
                    static fn (GeocodingServiceProvider $case) => $unsuccessfulResponse,
                    GeocodingServiceProvider::cases(),
                )
            );

        $result = $this->geocoderService->geocode(new Address('PL', 'city', 'street', 'postCode'));
        $this->assertNull($result);
    }

    /**
     * @throws Throwable
     */
    public function testItResolves(): void
    {
        $this->coordinatesFinder->method('get')->willReturn(null);
        $notFoundResponse = $this->createMock(ResponseInterface::class);
        $notFoundResponse->method('getStatusCode')->willReturn(404);
        $hereMapsResponse = $this->createMock(ResponseInterface::class);
        $hereMapsResponse->method('getStatusCode')->willReturn(200);
        $hereMapsResponse
            ->method('getBody')
            ->willReturn(HereMapsResponse::successfulResponse());

        $this->client
            ->method('sendRequest')
            ->willReturnCallback(
                static function (RequestInterface $request) use ($notFoundResponse, $hereMapsResponse) {
                    if(!str_contains((string) $request->getUri(), 'hereapi')) {
                        return $notFoundResponse;
                    }

                    return $hereMapsResponse;
                }
            );

        $result = $this->geocoderService->geocode(new Address('PL', 'city', 'street', 'postCode'));
        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }

    /**
     * @throws Throwable
     */
    public function testItResolvesByExactServiceProvider(): void
    {
        $this->coordinatesFinder->method('get')->willReturn(null);
        $hereMapsResponse = $this->createMock(ResponseInterface::class);
        $hereMapsResponse->method('getStatusCode')->willReturn(200);
        $hereMapsResponse
            ->method('getBody')
            ->willReturn(HereMapsResponse::successfulResponse());

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($hereMapsResponse);

        $result = $this->geocoderService->geocode(
            new Address(
                'PL',
                'city',
                'street',
                'postCode',
            ),
            GeocodingServiceProvider::HERE_MAPS,
        );
        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }

    public function testItDoesNotCallApiWhenCacheIsPresent(): void
    {
        $this->coordinatesFinder
            ->method('get')
            ->willReturn(
                new Coordinates('10.0001', '20.0002'),
            );

        $this->client
            ->expects($this->never())
            ->method('sendRequest');

        $result = $this->geocoderService->geocode(
            new Address(
                'PL',
                'city',
                'street',
                'postCode',
            ),
        );

        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);

        $result = $this->geocoderService->geocode(
            new Address(
                'PL',
                'city',
                'street',
                'postCode',
            ),
            GeocodingServiceProvider::GOOGLE_MAPS,
        );

        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }
}
