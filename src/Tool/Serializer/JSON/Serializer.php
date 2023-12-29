<?php

declare(strict_types=1);

namespace App\Tool\Serializer\JSON;

use App\Tool\Serializer\SerializerFailedException;

interface Serializer
{
    /**
     * @param object|mixed[] $data - Simple DTO serializable to JSON or associative array of data.
     *
     * @throws SerializerFailedException
     */
    public function serialize(object|array $data): string;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @throws SerializerFailedException
     *
     * @return T
     */
    public function deserialize(string $json, string $class): object;
}
