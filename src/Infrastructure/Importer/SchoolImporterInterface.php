<?php

declare(strict_types=1);

namespace App\Infrastructure\Importer;

use App\Domain\School;

interface SchoolImporterInterface
{
    /**
     * @return School[]
     */
    public function import(string $filePath): array;
}
