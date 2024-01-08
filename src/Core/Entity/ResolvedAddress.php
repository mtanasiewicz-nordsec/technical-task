<?php

declare(strict_types=1);

namespace App\Core\Entity;

use App\Core\Repository\ResolvedAddressRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResolvedAddressRepository::class)]
#[ORM\Table(name: 'resolved_addresses')]
#[ORM\Index(columns: ['created_at'], name: 'resolved_addresses_created_at_idx')]
#[ORM\Index(columns: ['hash'], name: 'resolved_addresses_hash_idx')]
class ResolvedAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\Column(type: 'string', length: 3)]
    private string $countryCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $city;

    #[ORM\Column(type: 'string', length: 255)]
    private string $street;

    #[ORM\Column(type: 'string', length: 100)]
    private string $postcode;

    #[ORM\Column(type: 'string')]
    private string $hash;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $lat;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $lng;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    private function __construct(
        string $countryCode,
        string $city,
        string $street,
        string $postcode,
        string $hash,
        ?string $lat = null,
        ?string $lng = null,
    ) {
        $this->countryCode = $countryCode;
        $this->city = $city;
        $this->street = $street;
        $this->postcode = $postcode;
        $this->hash = $hash;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->createdAt = new DateTime();
    }

    public static function create(
        string $countryCode,
        string $city,
        string $street,
        string $postcode,
        string $hash,
        ?string $lat = null,
        ?string $lng = null,
    ): self {
        return new self(
            $countryCode,
            $city,
            $street,
            $postcode,
            $hash,
            $lat,
            $lng,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
}
