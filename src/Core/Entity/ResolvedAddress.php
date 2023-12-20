<?php

declare(strict_types=1);

namespace App\Core\Entity;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Repository\ResolvedAddressRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResolvedAddressRepository::class)]
#[ORM\Table(name: 'resolved_addresses')]
#[ORM\Index(columns: ['country_code', 'city', 'street', 'postcode'], name: 'resolved_addresses_search_idx')]
#[ORM\Index(columns: ['created_at'], name: 'resolved_addresses_created_at_idx')]
class ResolvedAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\Column(type: 'string', length: 60, enumType: GeocodingServiceProvider::class)]
    private GeocodingServiceProvider $serviceProvider;

    #[ORM\Column(type: 'string', length: 3)]
    private string $countryCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $city;

    #[ORM\Column(type: 'string', length: 255)]
    private string $street;

    #[ORM\Column(type: 'string', length: 100)]
    private string $postcode;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8)]
    private string $lat;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8)]
    private string $lng;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    private function __construct(
        GeocodingServiceProvider $serviceProvider,
        string $countryCode,
        string $city,
        string $street,
        string $postcode,
        string $lat,
        string $lng,
    ) {
        $this->serviceProvider = $serviceProvider;
        $this->countryCode = $countryCode;
        $this->city = $city;
        $this->street = $street;
        $this->postcode = $postcode;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->createdAt = new DateTime();
    }

    public static function create(
        GeocodingServiceProvider $serviceProvider,
        string $countryCode,
        string $city,
        string $street,
        string $postcode,
        string $lat,
        string $lng,
    ): self {
        return new self(
            $serviceProvider,
            $countryCode,
            $city,
            $street,
            $postcode,
            $lat,
            $lng,
        );
    }
}
