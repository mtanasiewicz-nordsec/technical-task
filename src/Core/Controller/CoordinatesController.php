<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\DTO\GeocodeRequest;
use App\Core\DTO\GeocodeResponse;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Service\GeocoderService;
use App\Core\ValueObject\Address;
use App\Tool\Symfony\Controller\LoggableController;
use App\Tool\Symfony\Controller\Response\BadRequestResponse;
use App\Tool\Symfony\Controller\Response\NotFoundResponse;
use App\Tool\Symfony\Controller\RestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CoordinatesController extends RestController implements LoggableController
{
    public function __construct(
        private readonly GeocoderService $geocoder,
    ) {
    }

    #[OA\Tag('Geocoder')]
    #[OA\QueryParameter('countryCode', 'countryCode', 'ISO country code', required: true, allowEmptyValue: false)]
    #[OA\QueryParameter('city', 'city', 'City', required: true, allowEmptyValue: false)]
    #[OA\QueryParameter('street', 'street', 'Street address with building/apartment number', required: true, allowEmptyValue: false)]
    #[OA\QueryParameter('postCode', 'postCode', 'Postal code', required: true, allowEmptyValue: false)]
    #[OA\QueryParameter('serviceProvider', 'serviceProvider', 'Service provider to use', required: false, allowEmptyValue: false)]
    #[OA\Response(
        response: 200,
        description: 'Returns geocoded location of provided address.',
        content: new OA\JsonContent(
            ref: new Model(type: GeocodeResponse::class),
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation error response.',
        content: new OA\JsonContent(
            ref: new Model(type: BadRequestResponse::class),
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Returns information that we could not find geocoding information for this address.',
        content: new OA\JsonContent(
            ref: new Model(type: NotFoundResponse::class),
            type: 'object'
        )
    )]

    #[Route(path: '/coordinates', name: 'geocode')]
    #[ParamConverter('queryParameters', class: GeocodeRequest::class, converter: 'query_param_converter')]
    public function geocodeAction(GeocodeRequest $queryParameters): Response
    {
        $coordinates = $this->geocoder->geocode(
            new Address(
                $queryParameters->countryCode,
                $queryParameters->city,
                $queryParameters->street,
                $queryParameters->postcode,
            ),
            GeocodingServiceProvider::cases(),
        );

        if (null === $coordinates) {
            return $this->notFound('We were unable to find any coordinates matching your address.');
        }

        return $this->ok(
            new GeocodeResponse(
                $coordinates->lat,
                $coordinates->lng,
            ),
        );
    }
}
