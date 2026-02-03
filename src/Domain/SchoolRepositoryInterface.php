<?php

namespace App\Domain;

interface SchoolRepositoryInterface
{
    /**
     * @return School[]
     */
    public function findAll(): array;

    public function save(School $school): void;
}
