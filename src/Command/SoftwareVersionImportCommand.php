<?php

namespace App\Command;

use App\Entity\SoftwareVersion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:import-software-versions',
    description: 'Import software versions from JSON file',
)]
class SoftwareVersionImportCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        #[Autowire(param: 'kernel.project_dir')] string $projectDir
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $jsonPath = dirname($this->projectDir) . '/softwareversions.json';

        if (!file_exists($jsonPath)) {
            $io->error(sprintf('File not found: %s', $jsonPath));
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if ($data === null) {
            $io->error('Invalid JSON data');
            return Command::FAILURE;
        }

        $io->progressStart(count($data));

        foreach ($data as $item) {
            $version = new SoftwareVersion();
            $version->setName($item['name'] ?? '');
            $version->setSystemVersion($item['system_version'] ?? '');
            $version->setSystemVersionAlt($item['system_version_alt'] ?? '');
            $version->setLink($item['link'] ?? null);
            $version->setSt($item['st'] ?? null);
            $version->setGd($item['gd'] ?? null);
            $version->setLatest((bool)($item['latest'] ?? false));

            $this->entityManager->persist($version);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success('Software versions imported successfully!');

        return Command::SUCCESS;
    }
}
