<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SchoolMatchRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $schoolName,
    ) {
    }
}
