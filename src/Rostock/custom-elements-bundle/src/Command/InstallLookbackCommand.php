<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
use Contao\CalendarModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:install-lookback',
    description: 'Adds the PSA Lookback content element to the /events page.',
)]
class InstallLookbackCommand extends Command
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);

        $page = null;

        foreach (PageModel::findBy('alias', 'events') ?? [] as $candidate) {
            $page = $candidate;
            break;
        }

        if ($page === null) {
            $io->error('Events page not found. Run psa:install-events first.');

            return Command::FAILURE;
        }

        $calendar = CalendarModel::findOneBy('title', 'Events')
            ?? CalendarModel::findOneBy('title', 'PSA Events');

        if ($calendar === null) {
            $io->error('Events calendar not found. Run psa:install-events first.');

            return Command::FAILURE;
        }

        $article = null;

        foreach (ArticleModel::findBy('pid', (int) $page->id) ?? [] as $candidate) {
            $article = $candidate;
            break;
        }

        if ($article === null) {
            $io->error('No article found on /events.');

            return Command::FAILURE;
        }

        foreach (ContentModel::findBy('pid', (int) $article->id) ?? [] as $content) {
            if ($content->type === 'psa_lookback') {
                $content->lookback_calendar = (int) $calendar->id;
                $content->lookback_jumpTo = (int) $page->id;
                $content->lookback_scope = 'past';
                $content->lookback_year = (string) date('Y');
                $content->tstamp = time();
                $content->save();
                $io->writeln('Updated existing PSA Lookback element (id '.$content->id.').');

                return Command::SUCCESS;
            }
        }

        $content = new ContentModel();
        $content->pid = (int) $article->id;
        $content->ptable = 'tl_article';
        $content->type = 'psa_lookback';
        $content->sorting = 64;
        $content->published = '1';
        $content->headline = serialize(['unit' => 'h2', 'value' => 'The Lookback']);
        $content->subline = 'PSA Rostock';
        $content->lookback_calendar = (int) $calendar->id;
        $content->lookback_jumpTo = (int) $page->id;
        $content->lookback_scope = 'past';
        $content->lookback_year = (string) date('Y');
        $content->tstamp = time();
        $content->save();

        $io->success('PSA Lookback element created on /events (content id '.$content->id.').');

        return Command::SUCCESS;
    }
}
