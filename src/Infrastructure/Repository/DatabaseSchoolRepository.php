<?php

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
}
