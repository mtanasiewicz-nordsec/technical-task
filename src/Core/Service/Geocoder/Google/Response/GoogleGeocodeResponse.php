<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\Google\Response;

final readonly class GoogleGeocodeResponse
{
    /**
     * @param GoogleResult[] $results
     */
    public function __construct(
        /** @var GoogleResult[] */
        public array $results,
    ) {
    }

    public function isProcessable(): bool
    {
        return count($this->results) !== 0 && $this->results[0]->geometry->location_type === 'ROOFTOP';
    }

    public function getFirstLatitude(): float
    {
        return $this->results[0]->geometry->location->lat;
    }

    public function getFirstLongitude(): float
    {
        return $this->results[0]->geometry->location->lng;
    }
}
