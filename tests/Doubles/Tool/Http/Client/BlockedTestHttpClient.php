<?php

declare(strict_types=1);

namespace App\Tests\Doubles\Tool\Http\Client;

use LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Some other useful solutions if we need to send actual http requests in tests:
 * - https://wiremock.org/
 * - https://php-vcr.github.io/
 */
final class BlockedTestHttpClient implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        throw new LogicException('Sending actual http requests in tests is a bad idea.');
    }
}
