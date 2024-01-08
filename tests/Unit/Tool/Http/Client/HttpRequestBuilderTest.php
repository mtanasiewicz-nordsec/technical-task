<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tool\Http\Client;

use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpRequestBuilder;
use PHPUnit\Framework\Attributes\Test;

final class HttpRequestBuilderTest extends UnitTest
{
    private const URL = 'https://example.com';

    #[Test]
    public function getWhenProvidedUrlBuilderShouldReturnRequestWithGETMethod(): void
    {
        $request = HttpRequestBuilder::get(self::URL)->build();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    #[Test]
    public function postWhenProvidedUrlBuilderShouldReturnRequestWithPOSTMethod(): void
    {
        $request = HttpRequestBuilder::post(self::URL)->build();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    #[Test]
    public function putWhenProvidedUrlBuilderShouldReturnRequestWithPUTMethod(): void
    {
        $request = HttpRequestBuilder::put(self::URL)->build();

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    #[Test]
    public function patchWhenProvidedUrlBuilderShouldReturnRequestWithPATCHMethod(): void
    {
        $request = HttpRequestBuilder::patch(self::URL)->build();

        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    #[Test]
    public function deleteWhenProvidedUrlBuilderShouldReturnRequestWithDELETEMethod(): void
    {
        $request = HttpRequestBuilder::delete(self::URL)->build();

        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame(self::URL, (string) $request->getUri());
    }

    #[Test]
    public function withQueryParamsWhenProvidedArrayOfQueryParamsBuilderShouldReturnRequestWithSameQueryParams(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->withQueryParams([
                'qp1' => 'qv1',
                'qp2' => 'qv2',
            ])
            ->build();

        $this->assertSame('https://example.com?qp1=qv1&qp2=qv2', (string) $request->getUri());
    }

    #[Test]
    public function withQueryParamsWhenProvidedArrayOfQueryParamsTwiceBuilderShouldReturnRequestWithMergedQueryParams(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->withQueryParams([
                'qp1' => 'to-override',
                'qp2' => 'qv2',
            ])
            ->withQueryParams([
                'qp1' => 'qv1',
                'qp3' => 'qv3',
            ])
            ->build();

        $this->assertSame('https://example.com?qp1=qv1&qp2=qv2&qp3=qv3', (string) $request->getUri());
    }

    #[Test]
    public function addQueryParamWhenProvidedKeyAndValueBuilderShouldReturnRequestWithSameQueryParams(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->addQueryParam('qp1', 'qv1')
            ->addQueryParam('qp2', 'qv2')
            ->build();

        $this->assertSame('https://example.com?qp1=qv1&qp2=qv2', (string) $request->getUri());
    }

    #[Test]
    public function addQueryParamWhenProvidedSameKeyWithAnotherValueBuilderShouldReturnRequestWithOverriddenQueryParam(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->addQueryParam('qp1', 'qv1')
            ->addQueryParam('qp1', 'overridden')
            ->build();

        $this->assertSame('https://example.com?qp1=overridden', (string) $request->getUri());
    }

    #[Test]
    public function withHeadersWhenProvidedArrayOfHeadersBuilderShouldReturnRequestWithExpectedHeaders(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->withHeaders([
                'h1' => 'hv1',
                'h2' => 'hv2',
            ])
            ->build();

        $this->assertSame(
            [
                'Host' => ['example.com'],
                'h1' => ['hv1'],
                'h2' => ['hv2'],
            ],
            $request->getHeaders(),
        );
    }

    #[Test]
    public function withHeadersWhenProvidedArrayOfHeadersTwiceBuilderShouldReturnRequestWithMergedHeaders(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->withHeaders([
                'h1' => 'to-override',
                'h2' => 'hv2',
            ])
            ->withHeaders([
                'h1' => 'hv1',
                'h3' => 'hv3',
            ])
            ->build();

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

    #[Test]
    public function withBodyWhenProvidedBodyBuilderShouldReturnRequestWithSameBody(): void
    {
        $request = HttpRequestBuilder::post(self::URL)
            ->withBody('body')
            ->build();

        $this->assertSame('body', $request->getBody()->getContents());
    }
}
