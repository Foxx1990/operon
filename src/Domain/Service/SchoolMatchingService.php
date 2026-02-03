<?php

namespace App\Domain\Service;

use App\Domain\School;
use App\Domain\SchoolRepositoryInterface;

class SchoolMatchingService
{
    public function __construct(
        private readonly SchoolRepositoryInterface $schoolRepository
    ) {}

    public function match(string $userInput): ?School
    {
        $normalizedInput = $this->normalize($userInput);
        $inputLength = strlen($normalizedInput);
        $schools = $this->schoolRepository->findAll();
        
        $bestMatch = null;
        $lowestDistance = 1000;

        foreach ($schools as $school) {
            foreach ($school->getSearchTerms() as $term) {
                // 1. Exact match (O(1) comparison)
                if ($term === $normalizedInput) {
                    return $school;
                }

                // 2. Early skip for fuzzy match if length difference is too big
                // This saves expensive levenshtein() calls
                if (abs(strlen($term) - $inputLength) > 3) {
                    continue;
                }

                $distance = levenshtein($normalizedInput, $term);
                if ($distance < $lowestDistance) {
                    $lowestDistance = $distance;
                    $bestMatch = $school;
                }
            }
        }

        // 3. Threshold check
        $threshold = min(3, (int) ($inputLength * 0.3));

        return ($bestMatch && $lowestDistance <= $threshold) ? $bestMatch : null;
    }

    private function normalize(string $input): string
    {
        return strtolower(trim($input));
    }
}
