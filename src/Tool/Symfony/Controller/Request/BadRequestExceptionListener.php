<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use App\Tool\Symfony\Controller\Response\BadRequestResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @deprecated
 */
final class BadRequestExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof BadRequestHttpException) {
            return;
        }

        $event->stopPropagation();
        $event->setResponse(
            new JsonResponse(
                new BadRequestResponse($exception->messages),
                Response::HTTP_BAD_REQUEST,
            )
        );
    }
}
