<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\GeocoderService;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Unit\UnitTest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class GeocoderServiceTest extends UnitTest
{
    private GeocoderInterface&MockObject $geocoderMock;

    private LoggerInterface&MockObject $loggerMock;

    private GeocoderService $geocoderService;

    private Address $testAddress;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geocoderMock = $this->createMock(GeocoderInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->geocoderService = new GeocoderService(
            $this->geocoderMock,
            $this->loggerMock,
        );

        $this->testAddress = new Address('lt', 'city', 'street', 'postcode');
    }

    #[Test]
    public function geocodeWhenGeocoderReturnsCoordinatesShouldReturnSameCoordinates(): void
    {
        $testCoordinates = new Coordinates('10.01', '20.02');
        $this->geocoderMock->method('geocode')->willReturn($testCoordinates);

        $result = $this->geocoderService->geocode($this->testAddress);

        self::assertSame($testCoordinates->lat, $result->lat);
        self::assertSame($testCoordinates->lng, $result->lng);
    }

    #[Test]
    public function geocodeWhenGeocoderThrowsExceptionShouldLogError(): void
    {
        $this->geocoderMock->method('geocode')->willThrowException(new GeocodingFailedException());

        $this->loggerMock
            ->expects(self::once())
            ->method('error');

        $this->geocoderService->geocode($this->testAddress);
    }

    #[Test]
    public function geocodeWhenGeocoderThrowsExceptionShouldReturnNull(): void
    {
        $this->geocoderMock->method('geocode')->willThrowException(new GeocodingFailedException());

        $result = $this->geocoderService->geocode($this->testAddress);

        self::assertNull($result);
    }
}
