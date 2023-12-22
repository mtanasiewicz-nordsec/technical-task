<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\DTO\CoordinatesResponse;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Factory\CoordinatesResponseFactory;
use App\Core\Service\GeocoderService;
use App\Core\Service\RequestToAddressTransformer;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CoordinatesController extends AbstractController
{
    public function __construct(
        private readonly RequestToAddressTransformer $requestToAddressTransformer,
        private readonly GeocoderService $geocoder,
        private readonly CoordinatesResponseFactory $coordinatesResponseFactory,
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
        description: 'Returns geocoded location of provided address',
        content: new OA\JsonContent(
            ref: new Model(type: CoordinatesResponse::class, groups: ['full']),
            type: 'object'
        )
    )]
    #[Route(path: '/coordinates', name: 'geocode')]
    public function geocodeAction(Request $request): Response
    {
        $serviceProvider = $request->query->get('serviceProvider');
        if ($serviceProvider !== null) {
            $serviceProvider = GeocodingServiceProvider::tryFrom((string) $serviceProvider);
        }

        $address = $this->requestToAddressTransformer->transform($request);
        $coordinates = $this->geocoder->geocode($address, $serviceProvider);
        if (null === $coordinates) {
            return new JsonResponse([]);
        }

        return new JsonResponse(
            $this->coordinatesResponseFactory->createFromCoordinates($coordinates),
        );
    }
}
