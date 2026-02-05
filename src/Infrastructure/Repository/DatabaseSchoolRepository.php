<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\School;
use App\Domain\SchoolRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(SchoolRepositoryInterface::class)]
class DatabaseSchoolRepository extends ServiceEntityRepository implements SchoolRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, School::class);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(School $school): void
    {
        $this->getEntityManager()->persist($school);
        $this->getEntityManager()->flush();
    }

    public function findPotentialMatches(string $query, int $limit = 10): array
    {
        $rsm = $this->createResultSetMappingBuilder('s');
        
        $sql = '
            SELECT ' . $rsm->generateSelectClause() . '
            FROM schools s
            WHERE similarity(s.name::text, CAST(:query AS text)) > 0.3 
               OR similarity(s.aliases::text, CAST(:query AS text)) > 0.3
               OR s.name ILIKE CAST(:ilike_query AS text)
               OR s.aliases::text ILIKE CAST(:ilike_query AS text)
            ORDER BY GREATEST(
                similarity(s.name::text, CAST(:query AS text)),
                similarity(s.aliases::text, CAST(:query AS text))
            ) DESC
            LIMIT :limit
        ';

        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $nativeQuery->setParameter('query', $query);
        $nativeQuery->setParameter('ilike_query', '%' . $query . '%');
        $nativeQuery->setParameter('limit', $limit);

        return $nativeQuery->getResult();
    }
}
