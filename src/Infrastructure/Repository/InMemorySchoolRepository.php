<?php

namespace App\Infrastructure\Repository;

use App\Domain\School;
use App\Domain\SchoolRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

class InMemorySchoolRepository implements SchoolRepositoryInterface
{
    private array $schools = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%/data/schools.json')]
        string $waitingListPath
    ) {
        $this->loadSchools($waitingListPath);
    }

    private function loadSchools(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return;
        }

        foreach ($data as $item) {
            $this->schools[] = new School(
                $item['name'],
                $item['aliases'] ?? [],
                $item['city'],
                $item['type']
            );
        }
    }

    public function findAll(): array
    {
        return $this->schools;
    }

    public function save(School $school): void
    {
        $this->schools[] = $school;
    }
}
