<?php

declare(strict_types=1);

namespace App\Tests\Doubles\Tool\Http\Client;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class StreamMother
{
    public static function fromString(string $value): StreamInterface
    {
        return Utils::streamFor($value);
    }
}
