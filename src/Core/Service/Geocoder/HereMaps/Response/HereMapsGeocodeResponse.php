<?php

declare(strict_types=1);

namespace App\Core\Service\Geocoder\HereMaps\Response;

final readonly class HereMapsGeocodeResponse
{
    /**
     * @param HereMapsItem[] $items
     */
    public function __construct(
        /** @var HereMapsItem[] $items */
        public array $items
    ) {
    }

    public function isProcessable(): bool
    {
        return count($this->items) !== 0 && $this->items[0]->resultType === 'houseNumber';
    }

    public function getFirstLatitude(): float
    {
        return $this->items[0]->position->lat;
    }

    public function getFirstLongitude(): float
    {
        return $this->items[0]->position->lng;
    }
}
