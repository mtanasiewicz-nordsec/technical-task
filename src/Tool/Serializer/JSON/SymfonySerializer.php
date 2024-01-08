<?php

declare(strict_types=1);

namespace App\Tool\Serializer\JSON;

use App\Tool\Serializer\SerializerFailedException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer as VendorSerializer;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final class SymfonySerializer implements Serializer
{
    private SerializerInterface $serializer;

    public function __construct(
    ) {
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new ObjectNormalizer(
                propertyTypeExtractor: new PhpDocExtractor(),
            ),
            new PropertyNormalizer(
                propertyTypeExtractor: new PhpDocExtractor(),
            ),
            new ArrayDenormalizer(),
        ];

        $this->serializer = new VendorSerializer($normalizers, $encoders);
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
        try {
            return $this->serializer->deserialize($json, $class, 'json');
        } catch (Throwable $e) {
            throw new SerializerFailedException($e->getMessage(), previous: $e);
        }
    }
}
