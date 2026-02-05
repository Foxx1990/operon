<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\SchoolRepositoryInterface;
use App\Infrastructure\Importer\JsonSchoolImporter;
use App\Infrastructure\Importer\XlsSchoolImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:import-schools', description: 'Imports schools from JSON or XLS to database')]
class ImportSchoolsCommand extends Command
{
    public function __construct(
        private readonly SchoolRepositoryInterface $schoolRepository,
        private readonly JsonSchoolImporter $jsonImporter,
        private readonly XlsSchoolImporter $xlsImporter,
        #[Autowire('%kernel.project_dir%/data')]
        private readonly string $dataDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('format', InputArgument::REQUIRED, 'Import format: json or xls')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Filename (without extension)', 'schools');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = strtolower($input->getArgument('format'));
        $filename = $input->getArgument('filename');
        
        if (!in_array($format, ['json', 'xls'])) {
            $output->writeln('<error>Format must be "json" or "xls"</error>');
            return Command::FAILURE;
        }

        $filePath = $this->dataDir . '/' . $filename . '.' . $format;

        if (!file_exists($filePath)) {
            $output->writeln("<error>File not found: {$filePath}</error>");
            return Command::FAILURE;
        }

        try {
            $importer = $format === 'json' ? $this->jsonImporter : $this->xlsImporter;
            $schools = $importer->import($filePath);

            foreach ($schools as $school) {
                $this->schoolRepository->save($school);
            }

            $output->writeln("<info>Imported " . count($schools) . " schools from {$format}.</info>");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Import failed: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}
