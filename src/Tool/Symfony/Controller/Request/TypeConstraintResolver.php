<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use ReflectionType;
use Symfony\Component\Validator\Constraints\Type;

use function explode;

/**
 * @deprecated
 */
final readonly class TypeConstraintResolver
{
    public function resolve(?ReflectionType $reflectionType): ?Type
    {
        if ($reflectionType === null) {
            return null;
        }

        $resolvedTypes = explode('|', (string) $reflectionType);
        if (count($resolvedTypes) > 1) {
            return new Type($resolvedTypes);
        }

        return new Type($resolvedTypes[0]);
    }
}
