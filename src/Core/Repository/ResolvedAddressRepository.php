<?php

declare(strict_types=1);

namespace App\Core\Repository;

use App\Core\Entity\ResolvedAddress;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResolvedAddress>
 */
class ResolvedAddressRepository extends ServiceEntityRepository
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
}
