<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use DG\BypassFinals;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
    }
}
