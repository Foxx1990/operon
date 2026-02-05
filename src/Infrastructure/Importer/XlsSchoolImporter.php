<?php

declare(strict_types=1);

namespace App\Infrastructure\Importer;

use App\Domain\School;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class XlsSchoolImporter implements SchoolImporterInterface
{
    /**
     * @return School[]
     */
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Failed to load XLS file: " . $e->getMessage());
        }

        $schools = [];
        $headerRow = null;
        $rowIndex = 1;

        foreach ($worksheet->getRowIterator() as $row) {
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);
            $rowData = [];

            foreach ($cells as $cell) {
                $rowData[] = $cell->getValue();
            }

            if ($headerRow === null) {
                $headerRow = $rowData;
                continue;
            }

            if (empty(array_filter($rowData))) {
                continue; // Skip empty rows
            }

            $schoolData = array_combine($headerRow, $rowData);
            
            $schools[] = new School(
                name: $schoolData['name'] ?? '',
                aliases: $this->parseAliases($schoolData['aliases'] ?? ''),
                city: $schoolData['city'] ?? '',
                type: $schoolData['type'] ?? ''
            );
        }

        return $schools;
    }

    private function parseAliases(string $aliasesString): array
    {
        if (empty($aliasesString)) {
            return [];
        }

        // Split by comma, semicolon or newline
        $aliases = preg_split('/[,\n;]+/', $aliasesString);
        return array_map('trim', array_filter($aliases));
    }
}
