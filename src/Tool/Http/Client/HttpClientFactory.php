<?php

declare(strict_types=1);

namespace App\Tool\Http\Client;

use App\Tool\Http\Client\PSR\PSRClientFactory;

final readonly class HttpClientFactory
{
    public function __construct(
        private PSRClientFactory $clientFactory,
    ) {
    }

    public function create(): HttpClient
    {
        return new HttpClient($this->clientFactory->create());
    }
}
