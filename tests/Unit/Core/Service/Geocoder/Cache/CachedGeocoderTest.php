<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service\Geocoder\Cache;

use App\Core\Exception\GeocodingFailedException;
use App\Core\Service\Geocoder\Cache\CachedGeocoder;
use App\Core\Service\Geocoder\Cache\CoordinatesCacheInterface;
use App\Core\Service\Geocoder\GeocoderInterface;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use App\Tests\Unit\UnitTest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

final class CachedGeocoderTest extends UnitTest
{
    private MockObject&GeocoderInterface $decoratedGeocoder;

    private MockObject&CoordinatesCacheInterface $coordinatesCache;

    private Address $address;

    private Coordinates $coordinates;

    private CachedGeocoder $geocoder;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->decoratedGeocoder = $this->createMock(GeocoderInterface::class);
        $this->coordinatesCache = $this->createMock(CoordinatesCacheInterface::class);
        $this->address = new Address('country', 'city', 'street', 'postCode');
        $this->coordinates = new Coordinates('10.01', '20.02');

        $this->geocoder = new CachedGeocoder(
            $this->coordinatesCache,
            $this->decoratedGeocoder,
        );
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenCacheReturnsValueShouldReturnSameValue(): void
    {
        $this->coordinatesCache->method('get')->willReturn($this->coordinates);

        $result = $this->geocoder->geocode($this->address);

        $this->assertSame($this->coordinates->lat, $result->lat);
        $this->assertSame($this->coordinates->lng, $result->lng);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenCacheReturnsNoValueShouldCallDecoratedGeocoder(): void
    {
        $this->coordinatesCache->method('get')->willReturn(null);
        $this->decoratedGeocoder->method('geocode')->willReturn($this->coordinates);

        $this->decoratedGeocoder
            ->expects(self::once())
            ->method('geocode')
            ->with($this->address);

        $this->geocoder->geocode($this->address);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenCacheReturnsNoValueAndDecoratedGeocoderReturnsCoordinatesShouldStoreTheValueInCache(): void
    {
        $this->coordinatesCache->method('get')->willReturn(null);
        $this->decoratedGeocoder->method('geocode')->willReturn($this->coordinates);

        $this->coordinatesCache
            ->expects(self::once())
            ->method('store')
            ->with($this->address, $this->coordinates);

        $this->geocoder->geocode($this->address);
    }

    /**
     * @throws GeocodingFailedException
     */
    #[Test]
    public function geocodeWhenCacheReturnsNoValueAndGeocoderReturnsCoordinatesShouldReturnWhatGeocoderReturned(): void
    {
        $this->coordinatesCache->method('get')->willReturn(null);
        $this->decoratedGeocoder->method('geocode')->willReturn($this->coordinates);

        $result = $this->geocoder->geocode($this->address);

        $this->assertSame($this->coordinates->lat, $result->lat);
        $this->assertSame($this->coordinates->lng, $result->lng);
    }
}
