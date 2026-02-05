<?php

declare(strict_types=1);

namespace App\Infrastructure\Importer;

use App\Domain\School;

final class JsonSchoolImporter implements SchoolImporterInterface
{
    /**
     * @return School[]
     */
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $data = json_decode(file_get_contents($filePath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }

        $schools = [];
        foreach ($data as $schoolData) {
            $schools[] = new School(
                name: $schoolData['name'],
                aliases: $schoolData['aliases'] ?? [],
                city: $schoolData['city'] ?? '',
                type: $schoolData['type'] ?? ''
            );
        }

        return $schools;
    }
}
