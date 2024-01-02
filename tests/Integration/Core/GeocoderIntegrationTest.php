<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core;

use App\Tests\Integration\IntegrationTest;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GeocoderIntegrationTest extends IntegrationTest
{
    #[Test]
    public function coordinatesControllerGeocodeActionWhenCalledWithValidRequestShouldReturnExpectedResponse(): void
    {
        $this->client->jsonRequest(
            Request::METHOD_GET,
            '/api/coordinates?countryCode=pl&city=city&street=street&postcode=postcode',
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $this->assertSame(
            '{"lat":"10.01","lng":"20.02"}',
            $this->getClientResponse()->getContent(),
        );
    }

    #[Test]
    public function coordinatesControllerGeocodeActionWhenCalledWithInvalidRequestShouldReturnValidatedResponse(): void
    {
        $this->client->jsonRequest(
            Request::METHOD_GET,
            '/api/coordinates?countryCode=xd&city=city&street=street&postcode=postcode',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $this->assertSame(
            '{"messages":{"countryCode":["This value is not a valid country."]}}',
            $this->getClientResponse()->getContent(),
        );
    }
}
