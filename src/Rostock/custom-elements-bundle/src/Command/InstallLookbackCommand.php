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
    description: 'Adds or updates PSA Lookback content elements (events page and any missing jumpTo).',
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
                $this->updateLookbackContent($content, (int) $calendar->id, (int) $page->id);
                $io->writeln('Updated existing PSA Lookback element on /events (id '.$content->id.').');

                $fixed = $this->fixMissingJumpTo((int) $page->id, (int) $calendar->id);

                if ($fixed > 0) {
                    $io->writeln('Fixed lookback_jumpTo on '.$fixed.' other element(s).');
                }

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
        $this->updateLookbackContent($content, (int) $calendar->id, (int) $page->id);
        $content->save();

        $io->success('PSA Lookback element created on /events (content id '.$content->id.').');

        $fixed = $this->fixMissingJumpTo((int) $page->id, (int) $calendar->id);

        if ($fixed > 0) {
            $io->writeln('Fixed lookback_jumpTo on '.$fixed.' other element(s).');
        }

        return Command::SUCCESS;
    }

    private function updateLookbackContent(ContentModel $content, int $calendarId, int $eventsPageId): void
    {
        $content->lookback_calendar = $calendarId;
        $content->lookback_jumpTo = $eventsPageId;
        $content->lookback_scope = 'past';
        $content->lookback_year = (string) date('Y');
        $content->tstamp = time();
        $content->save();
    }

    private function fixMissingJumpTo(int $eventsPageId, int $calendarId): int
    {
        $fixed = 0;

        foreach (ContentModel::findBy('type', 'psa_lookback') ?? [] as $content) {
            if ((int) ($content->lookback_jumpTo ?? 0) > 0) {
                continue;
            }

            $content->lookback_jumpTo = $eventsPageId;

            if ((int) ($content->lookback_calendar ?? 0) <= 0) {
                $content->lookback_calendar = $calendarId;
            }

            $content->tstamp = time();
            $content->save();
            ++$fixed;
        }

        return $fixed;
    }
}
