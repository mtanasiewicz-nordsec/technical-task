<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Service;

use App\Core\Service\AddressHashGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddressHashGeneratorTest extends TestCase
{
    private string $expectedHash;

    private AddressHashGenerator $addressHashGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedHash = '4e42a0269315798ab7a69a62c07e9f0f';

        $this->addressHashGenerator = new AddressHashGenerator();
    }

    #[Test]
    public function generateWhenProvidedAddressShouldReturnHash(): void
    {
        $result = $this->addressHashGenerator->generate(
            'pl',
            'city',
            'street',
            'postcode'
        );

        $this->assertSame($this->expectedHash, $result);
    }

    #[Test]
    public function generateWhenProvidedAddressShouldReturnCaseInsensitiveHash(): void
    {
        $result = $this->addressHashGenerator->generate(
            'PL',
            'city',
            'STREET',
            'postCode'
        );

        $this->assertSame($this->expectedHash, $result);
    }

    #[Test]
    public function generateWhenProvidedAddressShouldReturnWhitespaceInsensitiveHash(): void
    {
        $result = $this->addressHashGenerator->generate(
            'pl',
            'city ',
            ' street',
            'post code'
        );

        $this->assertSame($this->expectedHash, $result);
    }

    #[Test]
    public function generateWhenProvidedDifferentAddressShouldReturnDifferentHash(): void
    {
        $result = $this->addressHashGenerator->generate(
            'pl',
            'city',
            'street2',
            'postcode'
        );

        $this->assertNotSame($this->expectedHash, $result);
    }
}
