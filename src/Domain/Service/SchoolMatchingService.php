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
        
        // 1. Get potential candidates from the database using trigram similarity
        // Optimized: only look at the top 20 candidates instead of thousands
        $schools = $this->schoolRepository->findPotentialMatches($userInput, 20);
        
        $bestMatch = null;
        $lowestDistance = 1000;

        foreach ($schools as $school) {
            foreach ($school->getSearchTerms() as $term) {
                // 2. Exact match (High priority)
                if ($term === $normalizedInput) {
                    return $school;
                }

                // 3. Early skip for fuzzy match if length difference is too big
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

        // 4. Threshold check for final match
        $threshold = min(3, (int) ($inputLength * 0.3));

        return ($bestMatch && $lowestDistance <= $threshold) ? $bestMatch : null;
    }

    private function normalize(string $input): string
    {
        return strtolower(trim($input));
    }
}
