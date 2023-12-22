<?php

declare(strict_types=1);

namespace App\Core\Finder;

use App\Core\Entity\ResolvedAddress;
use App\Core\Enum\GeocodingServiceProvider;
use App\Core\ValueObject\Address;
use App\Core\ValueObject\Coordinates;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use LogicException;

final readonly class DoctrineCoordinatesFinder implements CoordinatesFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(Address $address, ?GeocodingServiceProvider $serviceProvider = null): ?Coordinates
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('partial resolvedAddress.{id, lat, lng}')
            ->from(ResolvedAddress::class, 'resolvedAddress')
            ->andWhere('resolvedAddress.city = :city')
            ->setParameter('city', $address->city)
            ->andWhere('resolvedAddress.countryCode = :country')
            ->setParameter('country', $address->country)
            ->andWhere('resolvedAddress.postcode = :postcode')
            ->setParameter('postcode', $address->postcode)
            ->andWhere('resolvedAddress.street = :street')
            ->setParameter('street', $address->street);

        if ($serviceProvider !== null) {
            $queryBuilder->andWhere('resolvedAddress.serviceProvider = :serviceProvider')
                ->setParameter('serviceProvider', $serviceProvider);
        }

        try {
            /** @var array{id: int, lat: string, lng: string}|null $result */
            $result = $queryBuilder
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

            if ($result === null) {
                return null;
            }

            return new Coordinates(
                $result['lat'],
                $result['lng'],
            );
        } catch (NonUniqueResultException $e) {
            throw new LogicException(
                'Result should be unique with max results set to 1. Please check implementation.',
                previous: $e,
            );
        }
    }
}
