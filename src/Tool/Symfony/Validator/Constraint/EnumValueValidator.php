<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Validator\Constraint;

use Attribute;
use BackedEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use function array_map;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class EnumValueValidator extends ConstraintValidator
{
    /**
     * @param EnumValue $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof $constraint->enumClass || $value === null) {
            return;
        }

        $expectedValues = array_map(
            static fn (BackedEnum $enum) => $enum->value,
            $constraint->enumClass::cases(),
        );

        $implodedMessage = implode(',', $expectedValues);

        if (!in_array($value, $expectedValues, true)) {
            $this->context->addViolation(
                'Invalid value. Expected one of: ' . $implodedMessage,
            );
        }
    }
}
