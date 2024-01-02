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
final class ResolvedAddressRepository extends ServiceEntityRepository
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

    public function getFirstByHash(string $hash): ?ResolvedAddress
    {
        try {
            return $this->createQueryBuilder('resolvedAddress')
                ->where('resolvedAddress.hash = :hash')
                ->setParameter('hash', $hash)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new LogicException(
                'Query with max results as 1 should always return single record',
                previous: $e,
            );
        }
    }
}
