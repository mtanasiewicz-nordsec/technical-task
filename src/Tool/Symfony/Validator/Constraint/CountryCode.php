<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraints\Country;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class CountryCode extends Country
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly bool $caseSensitive = false,
        array $options = null,
        string $message = null,
        bool $alpha3 = null,
        array $groups = null,
        $payload = null
    ) {
        parent::__construct($options, $message, $alpha3, $groups, $payload);
    }
}
