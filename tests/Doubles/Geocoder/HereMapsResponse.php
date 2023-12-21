<?php

declare(strict_types=1);

namespace App\Tests\Doubles\Geocoder;

use App\Tests\Doubles\Tool\Http\Client\StreamMother;
use Psr\Http\Message\StreamInterface;

final class HereMapsResponse
{
    public static function successfulResponse(): StreamInterface
    {
        return StreamMother::fromString(
            <<<JSON
            {
               "items":[
                  {
                     "resultType":"houseNumber",
                     "houseNumberType":"PA",
                     "position":{
                        "lat":10.0001,
                        "lng":20.0002
                     }
                  }
               ]
            }
            JSON
        );
    }
}
