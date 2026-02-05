<?php

declare(strict_types=1);

namespace App\Domain;

interface SchoolRepositoryInterface
{
    /**
     * @return School[]
     */
    public function findAll(): array;

    /**
     * @return School[]
     */
    public function findPotentialMatches(string $query, int $limit = 10): array;

    public function save(School $school): void;
}
