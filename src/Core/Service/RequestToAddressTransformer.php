<?php

declare(strict_types=1);

namespace App\Core\Service;

use App\Core\ValueObject\Address;
use Symfony\Component\HttpFoundation\Request;

final readonly class RequestToAddressTransformer
{
    public function transform(Request $request): Address
    {
        $country = $request->get('countryCode', 'lt');
        $city = $request->get('city', 'vilnius');
        $street = $request->get('street', 'jasinskio 16');
        $postcode = $request->get('postCode', '01112');

        return new Address(
            mb_strtolower(trim($country)),
            mb_strtolower(trim($city)),
            mb_strtolower(trim($street)),
            mb_strtolower(trim($postcode)),
        );
    }
}
