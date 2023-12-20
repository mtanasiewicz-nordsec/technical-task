<?php

declare(strict_types=1);

namespace App\Tool\Http\Client;

use GuzzleHttp\Client;

final readonly class HttpClientFactory
{
    public function create(): HttpClient
    {
        return new HttpClient(new Client());
    }
}
