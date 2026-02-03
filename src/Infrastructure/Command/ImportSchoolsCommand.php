<?php

namespace App\Infrastructure\Command;

use App\Domain\School;
use App\Domain\SchoolRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:import-schools', description: 'Imports schools from JSON to database')]
class ImportSchoolsCommand extends Command
{
    public function __construct(
        private readonly SchoolRepositoryInterface $schoolRepository,
        #[Autowire('%kernel.project_dir%/data/schools.json')]
        private readonly string $jsonPath
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists($this->jsonPath)) {
            $output->writeln('Data file not found.');
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($this->jsonPath), true);
        
        foreach ($data as $item) {
            $school = new School(
                $item['name'],
                $item['aliases'],
                $item['city'],
                $item['type']
            );
            $this->schoolRepository->save($school);
        }

        $output->writeln('Imported ' . count($data) . ' schools.');
        
        return Command::SUCCESS;
    }
}
