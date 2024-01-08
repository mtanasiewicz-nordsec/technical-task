<?php

declare(strict_types=1);

namespace App\Tests\Doubles\Tool\Serializer;

use App\Tool\Serializer\JSON\Serializer;
use App\Tool\Serializer\JSON\SymfonySerializer;

final class SerializerMother
{
    public static function create(): Serializer
    {
        return new SymfonySerializer();
    }
}
