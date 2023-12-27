<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use App\Tool\Serializer\JsonObjectSerializer;
use App\Tool\Serializer\SerializerFailedException;
use App\Tool\Symfony\Controller\Response\BadRequestResponse;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QueryParamConverter implements ParamConverterInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly JsonObjectSerializer $serializer,
    ) {
    }

    /**
     * @throws SerializerFailedException
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

        $object = $this->serializer->deserialize($this->serializer->serialize($data), $class);

        $violations = $this->validator->validate($object);
        if ($violations->count() === 0) {
            $request->attributes->set($configuration->getName(), $object);
            return true;
        }

        $messages = [];
        foreach ($violations as $violation) {
            $messages[$violation->getPropertyPath()][] = (string) $violation->getMessage();
        }

        $response = new JsonResponse(
            new BadRequestResponse($messages),
            Response::HTTP_BAD_REQUEST,
        );
        $response->prepare($request);
        $response->send();

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getConverter() === 'query_param_converter';
    }
}
