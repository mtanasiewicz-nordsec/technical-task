<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Validator\Constraint;

use Attribute;
use BackedEnum;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class EnumValue extends Constraint
{
    /**
     * @param class-string<BackedEnum> $enumClass
     */
    public function __construct(
        public readonly string $enumClass,
        $options = null,
        array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
