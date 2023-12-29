<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Service;

use App\Core\Entity\ResolvedAddress;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Repository\DoctrineResolvedAddressRepository;
use App\Core\Repository\ResolvedAddressRepository;
use App\Core\Service\GeocoderService;
use App\Core\ValueObject\Address;
use App\Tests\Doubles\Geocoder\HereMapsResponse;
use App\Tests\Integration\IntegrationTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class GeocoderServiceTest extends IntegrationTest
{
    private MockObject&ClientInterface $client;

    private MockObject&ResolvedAddressRepository $resolvedAddressRepository;

    private GeocoderService $geocoderService;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->resolvedAddressRepository = $this->createMock(ResolvedAddressRepository::class);
        self::getContainer()->set(DoctrineResolvedAddressRepository::class, $this->resolvedAddressRepository);

        $this->client = $this->createMock(ClientInterface::class);
        self::getContainer()->set('http_client.loggable', $this->client);

        $this->geocoderService = self::getContainer()->get(GeocoderService::class);
    }

    /**
     * @throws Throwable
     */
    public function testItTriesToResolveAllServices(): void
    {
        $this->resolvedAddressRepository->method('getFirstByHashAndProviders')->willReturn(null);
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

        $result = $this->geocoderService->geocode(
            new Address('PL', 'city', 'street', 'postCode'),
            GeocodingServiceProvider::cases(),
        );
        $this->assertNull($result);
    }

    /**
     * @throws Throwable
     */
    public function testItResolves(): void
    {
        $this->resolvedAddressRepository->method('getFirstByHashAndProviders')->willReturn(null);
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

        $result = $this->geocoderService->geocode(
            new Address('PL', 'city', 'street', 'postCode'),
            GeocodingServiceProvider::cases(),
        );
        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }

    /**
     * @throws Throwable
     */
    public function testItResolvesByExactServiceProvider(): void
    {
        $this->resolvedAddressRepository->method('getFirstByHashAndProviders')->willReturn(null);
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
            [GeocodingServiceProvider::HERE_MAPS],
        );
        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }

    public function testItDoesNotCallApiWhenCacheIsPresent(): void
    {
        $resolvedAddressMock = $this->createMock(ResolvedAddress::class);
        $resolvedAddressMock->method('getLat')->willReturn('10.0001');
        $resolvedAddressMock->method('getLng')->willReturn('20.0002');

        $this->resolvedAddressRepository
            ->method('getFirstByHashAndProviders')
            ->willReturn(
                $resolvedAddressMock,
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
            [GeocodingServiceProvider::GOOGLE_MAPS],
        );

        $this->assertSame('10.0001', $result->lat);
        $this->assertSame('20.0002', $result->lng);
    }
}
