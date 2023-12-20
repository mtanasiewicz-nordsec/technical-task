<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\Enum\GeocodingServiceProvider;
use App\Core\Factory\CoordinatesResponseFactory;
use App\Core\Service\GeocoderService;
use App\Core\Service\RequestToAddressTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoordinatesController extends AbstractController
{
    public function __construct(
        private readonly RequestToAddressTransformer $requestToAddressTransformer,
        private readonly GeocoderService $geocoder,
        private readonly CoordinatesResponseFactory $coordinatesResponseFactory,
    ) {
    }

    #[Route(path: '/coordinates', name: 'geocode')]
    public function geocodeAction(Request $request): Response
    {
        return $this->handleRequest($request);
    }

    #[Route(path: '/gmaps', name: 'gmaps')]
    public function gmapsAction(Request $request): Response
    {
        return $this->handleRequest($request, GeocodingServiceProvider::GOOGLE_MAPS);
    }

    #[Route(path: '/hmaps', name: 'hmaps')]
    public function hmapsAction(Request $request): Response
    {
        return $this->handleRequest($request, GeocodingServiceProvider::HERE_MAPS);
    }

    private function handleRequest(Request $request, ?GeocodingServiceProvider $serviceProvider = null): JsonResponse
    {
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
