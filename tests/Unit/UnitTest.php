<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use DG\BypassFinals;
use PHPUnit\Framework\TestCase;

abstract class UnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
    }
}
