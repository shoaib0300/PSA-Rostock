<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Rostock\CustomElementsBundle\Classes\PsaSeoAssetGenerator;
use Rostock\CustomElementsBundle\Classes\PsaSeoAssetStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:generate-seo-assets',
    description: 'Generate favicons, Open Graph image and web manifest for PSA Rostock.',
)]
class GenerateSeoAssetsCommand extends Command
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->framework->initialize();

        $targetDir = PsaSeoAssetStorage::ensureFolder();

        PsaSeoAssetGenerator::generateAll($targetDir);

        $io->success('SEO assets generated in files/favicons/');

        return Command::SUCCESS;
    }
}
