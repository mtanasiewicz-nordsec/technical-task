<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tool\Http\Client;

use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpResponse;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class HttpResponseTest extends UnitTest
{
    private ResponseInterface&MockObject $responseMock;

    /**
     * @throws Exception
     */
    public function testGetHeaders(): void
    {
        $response = $this->response();

        $expectedHeaders = ['h' => 'hv'];
        $this->responseMock->method('getHeaders')->willReturn($expectedHeaders);

        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    /**
     * @throws Exception
     */
    public function testIsSuccessful(): void
    {
        $response = $this->response();
        $this->responseMock->method('getStatusCode')->willReturn(200);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());

        $response = $this->response();
        $this->responseMock->method('getStatusCode')->willReturn(201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());

        $response = $this->response();
        $this->responseMock->method('getStatusCode')->willReturn(400);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testAccessMethods(): void
    {
        $response = $this->response();

        $streamMock = $this->createMock(StreamInterface::class);
        $this->responseMock->method('getBody')->willReturn($streamMock);
        $this->assertSame($streamMock, $response->getBody());

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->assertSame(200, $response->getStatusCode());

        $this->responseMock->method('getHeaders')->willReturn(['h1' => 'hv1']);
        $this->assertSame(['h1' => 'hv1'], $response->getHeaders());

        $this->responseMock->method('getHeader')->willReturn(['header']);
        $this->assertSame(['header'], $response->getHeader('name'));

        $this->responseMock->method('getHeaderLine')->willReturn('line');
        $this->assertSame('line', $response->getHeaderLine('name'));

        $this->responseMock->method('getReasonPhrase')->willReturn('reasonPhrase');
        $this->assertSame('reasonPhrase', $response->getReasonPhrase());

        $this->responseMock->method('getProtocolVersion')->willReturn('protocolVersion');
        $this->assertSame('protocolVersion', $response->getProtocolVersion());
    }

    /**
     * @throws Exception
     */
    private function response(): HttpResponse
    {
        $this->responseMock = $this->createMock(ResponseInterface::class);
        return new HttpResponse($this->responseMock);
    }
}
