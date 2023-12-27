<?php

declare(strict_types=1);

namespace App\Core\Repository;

use App\Core\Entity\ResolvedAddress;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<ResolvedAddress>
 */
final class DoctrineResolvedAddressRepository extends ServiceEntityRepository implements ResolvedAddressRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResolvedAddress::class);
    }

    public function save(ResolvedAddress $resolvedAddress): void
    {
        $this->getEntityManager()->persist($resolvedAddress);
        $this->getEntityManager()->flush();
    }

    public function removeOlderThan(DateTime $cutoff): void
    {
        $this->createQueryBuilder('resolvedAddress')
            ->delete()
            ->where('resolvedAddress.createdAt <= :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public function getFirstByHashAndProviders(
        string $hash,
        array $serviceProviders
    ): ?ResolvedAddress {
        $qb = $this->createQueryBuilder('resolvedAddress')
            ->where('resolvedAddress.hash = :hash')
            ->setParameter('hash', $hash)
            ->setMaxResults(1);

        if (count($serviceProviders) > 0) {
            $qb->andWhere('resolvedAddress.serviceProvider IN (:serviceProviders)')
                ->setParameter('serviceProviders', $serviceProviders);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new LogicException(
                'Query with max results as 1 should always return single record',
                previous: $e,
            );
        }
    }
}
