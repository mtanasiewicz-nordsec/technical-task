<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use App\Tool\Serializer\JSON\Serializer;
use App\Tool\Serializer\SerializerFailedException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function str_replace;

/**
 * @deprecated - will be replaced with Symfony 6.x (https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects)
 */
final readonly class QueryParamConverter implements ParamConverterInterface
{
    public function __construct(
        private ConstraintExtractor $constraintExtractor,
        private ValidatorInterface $validator,
        private Serializer $serializer,
    ) {
    }

    /**
     * @throws SerializerFailedException
     * @throws BadRequestHttpException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();
        if (!class_exists($class)) {
            throw new RuntimeException(
                'You should pass valid Fully Qualified Class Name as class argument to the #[ParamConverter] attribute'
            );
        }

        $data = [];
        foreach ($request->query->all() as $key => $value) {
            $data[$key] = $value;
        }

        $constraints = $this->constraintExtractor->extract($class);

        $violations = $this->validator->validate($data, $constraints);
        if ($violations->count() === 0) {
            $object = $this->serializer->deserialize($this->serializer->serialize($data), $class);

            $request->attributes->set($configuration->getName(), $object);
            return true;
        }

        $messages = [];
        foreach ($violations as $violation) {
            $messages[str_replace(['[', ']'], '', $violation->getPropertyPath())][] = (string) $violation->getMessage();
        }

        throw new BadRequestHttpException($messages);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getConverter() === 'query_param_converter';
    }
}
