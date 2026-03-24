<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SoftwareVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SoftwareVersion>
 */
class SoftwareVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoftwareVersion::class);
    }

    /**
     * Returns all versions whose system_version_alt matches the supplied string
     * (case-insensitive). The caller is responsible for filtering further by
     * LCI / hardware-type rules.
     *
     * @return SoftwareVersion[]
     */
    public function findByVersionAlt(string $versionAlt): array
    {
        return $this->createQueryBuilder('sv')
            ->where('LOWER(sv.systemVersionAlt) = LOWER(:version)')
            ->setParameter('version', $versionAlt)
            ->orderBy('sv.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
