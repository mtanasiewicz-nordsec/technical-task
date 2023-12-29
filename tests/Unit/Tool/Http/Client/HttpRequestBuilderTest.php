<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tool\Http\Client;

use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpRequestBuilder;

final class HttpRequestBuilderTest extends UnitTest
{
    private const URL = 'https://example.com';

    public function testGet(): void
    {
        $request = HttpRequestBuilder::GET(self::URL)->build();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    public function testPost(): void
    {
        $request = HttpRequestBuilder::POST(self::URL)->build();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    public function testPut(): void
    {
        $request = HttpRequestBuilder::PUT(self::URL)->build();

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    public function testPatch(): void
    {
        $request = HttpRequestBuilder::PATCH(self::URL)->build();

        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    public function testDelete(): void
    {
        $request = HttpRequestBuilder::DELETE(self::URL)->build();

        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    public function testBuildWithAllArguments(): void
    {
        $request = HttpRequestBuilder::POST(self::URL)
            ->withQueryParams([
                'qp1' => 'qv1',
                'qp2' => 'qv2',
            ])
            ->addQueryParam('qp3', 'qv3')
            ->withHeaders([
                'h1' => 'hv1',
                'h2' => 'hv2',
            ])
            ->addHeader('h3', 'hv3')
            ->build();

        $this->assertSame('https://example.com?qp1=qv1&qp2=qv2&qp3=qv3', (string) $request->getUri());
        $this->assertSame(
            [
                'Host' => ['example.com'],
                'h1' => ['hv1'],
                'h2' => ['hv2'],
                'h3' => ['hv3'],
            ],
            $request->getHeaders(),
        );
    }
}
