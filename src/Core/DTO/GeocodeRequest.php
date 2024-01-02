<?php

declare(strict_types=1);

namespace App\Core\DTO;

use App\Tool\Symfony\Validator\Constraint\CountryCode;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GeocodeRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[CountryCode]
        public string $countryCode,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public string $city,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public string $street,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public string $postcode,
    ) {
    }
}
