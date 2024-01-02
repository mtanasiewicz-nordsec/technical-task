<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\Stack;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\Service\Geocoder\Stack\GeocoderStack;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Unit\UnitTest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class GeocoderStackTest extends UnitTest
{
    private LoggerInterface&MockObject $loggerMock;

    private GeocoderInterface $exceptionGeocoderMock;

    private GeocoderInterface $nullGeocoderMock;

    private GeocoderInterface $geocoderMock;

    private Address $testAddress;

    private Coordinates $testCoordinates;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->exceptionGeocoderMock = $this->createMock(GeocoderInterface::class);
        $this->exceptionGeocoderMock->method('geocode')->willThrowException(new GeocodingFailedException());

        $this->nullGeocoderMock = $this->createMock(GeocoderInterface::class);
        $this->nullGeocoderMock->method('geocode')->willReturn(null);

        $this->geocoderMock = $this->createMock(GeocoderInterface::class);
        $this->testCoordinates = new Coordinates('10.01', '20.02');
        $this->geocoderMock->method('geocode')->willReturn($this->testCoordinates);

        $this->testAddress = new Address('country', 'city', 'street', 'postcode');
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenStackHasOneGeocoderThatReturnsCoordinatesShouldReturnThoseCoordinates(): void
    {
        $geocoder = $this->geocoder([
            $this->nullGeocoderMock,
            $this->nullGeocoderMock,
            $this->geocoderMock,
            $this->nullGeocoderMock,
        ]);

        $result = $geocoder->geocode($this->testAddress);

        $this->assertSame($this->testCoordinates, $result);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenAllGeocodersReturnNullShouldReturnNull(): void
    {
        $geocoder = $this->geocoder([
            $this->nullGeocoderMock,
            $this->nullGeocoderMock,
            $this->nullGeocoderMock,
        ]);

        $result = $geocoder->geocode($this->testAddress);

        $this->assertNull($result);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenSomeGeocodersThrowExceptionShouldLog(): void
    {
        $geocoder = $this->geocoder([
            $this->nullGeocoderMock,
            $this->exceptionGeocoderMock,
            $this->exceptionGeocoderMock,
            $this->nullGeocoderMock,
        ]);

        $this->loggerMock->expects($this->exactly(2))->method('error');

        $geocoder->geocode($this->testAddress);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenHasNoGeocodersShouldReturnNull(): void
    {
        $geocoder = $this->geocoder([]);

        $result = $geocoder->geocode($this->testAddress);

        $this->assertNull($result);
    }

    /**
     * @param GeocoderInterface[] $geocoders
     */
    private function geocoder(array $geocoders): GeocoderStack
    {
        return new GeocoderStack($geocoders, $this->loggerMock);
    }
}
