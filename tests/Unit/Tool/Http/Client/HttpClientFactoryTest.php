<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tool\Http\Client;

use App\Tests\Unit\UnitTest;
use App\Tool\Http\Client\HttpClientFactory;
use App\Tool\Http\Client\PSR\GuzzlePSRClientFactory;
use GuzzleHttp\Client;
use ReflectionClass;
use ReflectionException;

final class HttpClientFactoryTest extends UnitTest
{
    /**
     * @throws ReflectionException
     */
    public function testCreate(): void
    {
        $factory = new HttpClientFactory(new GuzzlePSRClientFactory());

        $httpClient = $factory->create();

        $reflection = new ReflectionClass($httpClient);

        $decoratedClient = $reflection
            ->getProperty('inner')
            ->getValue($httpClient);

        $this->assertInstanceOf(Client::class, $decoratedClient);
    }
}
