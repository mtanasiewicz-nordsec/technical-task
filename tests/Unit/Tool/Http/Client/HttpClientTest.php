<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tool\Http\Client;

use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpClient;
use App\Tool\Http\Client\HttpResponse;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpClientTest extends UnitTest
{
    private ClientInterface&MockObject $vendorClient;

    private HttpClient $client;

    public function setUp(): void
    {
        $this->vendorClient = $this->createMock(ClientInterface::class);
        $this->client = new HttpClient($this->vendorClient);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function testSendRequest(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $this->vendorClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($requestMock)
            ->willReturn($responseMock);

        $response = $this->client->sendRequest($requestMock);
        $this->assertInstanceOf(HttpResponse::class, $response);
    }
}
