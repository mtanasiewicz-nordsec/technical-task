<?php

declare(strict_types=1);

namespace App\Tool\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final readonly class HttpClient implements ClientInterface
{
    public function __construct(
        private ClientInterface $inner,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): HttpResponse
    {
        return new HttpResponse($this->inner->sendRequest($request));
    }
}
