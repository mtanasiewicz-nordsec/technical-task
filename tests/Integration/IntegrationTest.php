<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class IntegrationTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        self::bootKernel([
            'environment' => 'test',
            'debug'       => false,
        ]);
    }

    public function getClientResponse(): Response
    {
        return $this->client->getResponse();
    }
}
