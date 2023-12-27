<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller;

use App\Tool\Symfony\Controller\Response\NotFoundResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class RestController extends AbstractController
{
    /**
     * @param array<object>|object $data - any simple response DTO or array of Response DTO's with public properties
     * serializable to JSON.
     */
    public function ok(array|object|null $data = null): Response
    {
        return new JsonResponse(
            $data,
            Response::HTTP_OK,
        );
    }

    /**
     * @param string $message - optional simple message explaining why the resource was not found.
     */
    public function notFound(string $message = ''): Response
    {
        return new JsonResponse(
            new NotFoundResponse($message),
            Response::HTTP_NOT_FOUND,
        );
    }
}
