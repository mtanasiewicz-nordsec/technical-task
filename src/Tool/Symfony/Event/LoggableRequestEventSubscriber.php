<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Event;

use App\Tool\Symfony\Controller\LoggableRequestController;
use App\Tool\Symfony\Controller\LoggableResponseController;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class LoggableRequestEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ControllerResolverInterface $controllerResolver,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $controller = $this->controllerResolver->getController($event->getRequest());
        if ($controller === false) {
            return;
        }

        if (!is_array($controller) || !$controller[0] instanceof LoggableRequestController) {
            return;
        }

        $this->logger->debug(
            'Received HTTP request',
            [
                'method' => $event->getRequest()->getMethod(),
                'headers' => $event->getRequest()->headers->all(),
                'url' => $event->getRequest()->getUri(),
                'body' => $event->getRequest()->getContent(true),
            ]
        );
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $controller = $this->controllerResolver->getController($event->getRequest());
        if ($controller === false) {
            return;
        }

        if (!is_array($controller) || !$controller[0] instanceof LoggableResponseController) {
            return;
        }

        $this->logger->debug(
            'Returned HTTP response',
            [
                'headers' => $event->getResponse()->headers->all(),
                'body' => $event->getResponse()->getContent(),
            ]
        );
    }
}
