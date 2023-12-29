<?php

declare(strict_types=1);

namespace App\Tool\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonyJsonObjectSerializer implements JsonObjectSerializer
{
    private SerializerInterface $serializer;

    public function __construct(
    ) {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @inheritDoc
     */
    public function serialize(object|array $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $json, string $class): object
    {
        return $this->serializer->deserialize($json, $class, 'json');
    }
}
