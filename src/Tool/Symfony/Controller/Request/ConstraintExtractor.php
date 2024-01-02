<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @deprecated
 */
final readonly class ConstraintExtractor
{
    public function __construct(
        private TypeConstraintResolver $typeConstraintResolver,
    ) {
    }

    /**
     * @param class-string $class
     */
    public function extract(string $class): Collection
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException('"class argument should be a Fully Qualified Class Name"');
        }

        $constraints = [];
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $reflectionAttributes = $reflectionProperty->getAttributes();

            $typeConstraint = $this->typeConstraintResolver->resolve($reflectionProperty->getType());
            if ($typeConstraint !== null) {
                $constraints[$propertyName][] = $typeConstraint;
            }

            foreach ($reflectionAttributes as $reflectionAttribute) {
                $attribute = $reflectionAttribute->newInstance();
                if (!is_a($attribute, Constraint::class)) {
                    continue;
                }

                $constraints[$propertyName][] = $attribute;
            }
        }

        return new Collection($constraints);
    }
}
