<?php

declare(strict_types=1);

namespace App\Tool\Http\Client;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

final class HttpRequestBuilder
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * @var array<string, string>
     */
    private array $queryParams = [];

    private ?string $body = null;

    private function __construct(
        private readonly string $method,
        private readonly string $uri,
    ) {
    }

    public static function get(string $uri): self
    {
        return (new self('GET', $uri));
    }

    public static function post(string $uri): self
    {
        return (new self('POST', $uri));
    }

    public static function put(string $uri): self
    {
        return (new self('PUT', $uri));
    }

    public static function patch(string $uri): self
    {
        return (new self('PATCH', $uri));
    }

    public static function delete(string $uri): self
    {
        return (new self('DELETE', $uri));
    }

    /**
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $headerName => $headerValue) {
            $this->addHeader($headerName, $headerValue);
        }

        return $this;
    }

    public function addHeader(string $headerName, string $headerValue): self
    {
        $this->headers[$headerName] = $headerValue;

        return $this;
    }

    /**
     * @param array<string, string> $queryParams
     */
    public function withQueryParams(array $queryParams): self
    {
        foreach ($queryParams as $name => $value) {
            $this->addQueryParam($name, $value);
        }

        return $this;
    }

    public function addQueryParam(string $name, string $value): self
    {
        $this->queryParams[$name] = $value;

        return $this;
    }

    public function withBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function build(): RequestInterface
    {
        $request = new Request(
            $this->method,
            $this->uri,
        );

        $uri = $request->getUri();
        foreach ($this->queryParams as $key => $value) {
            $uri = Uri::withQueryValue($uri, $key, $value);
        }
        $request = $request->withUri($uri);

        foreach ($this->headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if ($this->body !== null) {
            $request = $request->withBody(Utils::streamFor($this->body));
        }

        return $request;
    }
}
