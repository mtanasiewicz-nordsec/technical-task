<?php

declare(strict_types=1);

namespace App\Tool\Http\Client\PSR;

use Psr\Http\Client\ClientInterface;

interface PSRClientFactory
{
    public function create(): ClientInterface;
}
