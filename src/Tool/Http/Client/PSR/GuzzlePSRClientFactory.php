<?php

declare(strict_types=1);

namespace App\Tool\Http\Client\PSR;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

final class GuzzlePSRClientFactory implements PSRClientFactory
{
    public function create(): ClientInterface
    {
        return new Client();
    }
}
