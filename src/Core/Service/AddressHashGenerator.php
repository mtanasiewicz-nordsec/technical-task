<?php

declare(strict_types=1);

namespace App\Core\Service;

final readonly class AddressHashGenerator
{
    public function generate(
        string $country,
        string $city,
        string $street,
        string $postcode,
    ): string {
        return md5(
            mb_strtolower(
                str_replace(
                    ' ',
                    '',
                    "$country$city$street$postcode"
                ),
            ),
        );
    }
}
