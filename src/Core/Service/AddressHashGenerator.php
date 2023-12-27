<?php

declare(strict_types=1);

namespace App\Core\Service;

final class AddressHashGenerator
{
    public function generate(
        string $country,
        string $city,
        string $street,
        string $postcode,
    ): string {
        return md5(
            mb_strtolower(
                "$country $city $street $postcode",
            ),
        );
    }
}
