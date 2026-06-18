<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
use Contao\CalendarModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Contao\PageModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:install-events',
    description: 'Create PSA events calendar, /events page, list and reader modules with member RSVP.',
)]
class InstallEventsCommand extends Command
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->framework->initialize();

        $calendarId = $this->ensureCalendar($io);
        $eventsPageId = $this->ensurePage($io, 'events', 'Events', false, null);
        $readerModuleId = $this->ensureModule($io, 'PSA Event Reader', 'eventreader', [
            'cal_calendar' => serialize([$calendarId]),
            'cal_template' => 'event_full_psa',
            'customTpl' => 'mod_eventreader_psa',
            'com_template' => 'com_default_psa',
            'headline' => '',
        ]);
        $listModuleId = $this->ensureModule($io, 'PSA Event List', 'eventlist', [
            'cal_calendar' => serialize([$calendarId]),
            'cal_format' => 'next_all',
            'cal_order' => 'ascending',
            'cal_limit' => '0',
            'cal_noSpan' => '1',
            'cal_readerModule' => $readerModuleId,
            'cal_template' => 'event_list_psa',
            'customTpl' => 'mod_eventlist_psa',
            'headline' => serialize(['unit' => 'h1', 'value' => 'Upcoming events']),
        ]);
        $this->ensureModule($io, 'PSA Past Event List', 'eventlist', [
            'cal_calendar' => serialize([$calendarId]),
            'cal_format' => 'past_all',
            'cal_order' => 'descending',
            'cal_limit' => '0',
            'cal_noSpan' => '1',
            'cal_readerModule' => $readerModuleId,
            'cal_template' => 'event_list_psa',
            'customTpl' => 'mod_eventlist_past_psa',
            'headline' => serialize(['unit' => 'h2', 'value' => 'Past events']),
        ]);

        $this->ensureArticleWithModule($io, $eventsPageId, 'events', 'Events', $listModuleId);
        $this->cleanupEventsPage($io, $eventsPageId, $listModuleId, $readerModuleId);

        $calendar = CalendarModel::findById($calendarId);

        if ($calendar !== null) {
            $calendar->jumpTo = $eventsPageId;
            $calendar->allowComments = '1';
            $calendar->requireLogin = '1';
            $calendar->disableCaptcha = '1';
            $calendar->sortOrder = 'descending';
            $calendar->tstamp = time();
            $calendar->save();
            $io->writeln('Calendar comments enabled (members only).');
        }

        $io->success(sprintf(
            'Events setup ready. Page: /events (%d), calendar id: %d, list module: %d, reader module: %d. Run `php vendor/bin/contao-console contao:migrate` if the RSVP table is missing.',
            $eventsPageId,
            $calendarId,
            $listModuleId,
            $readerModuleId,
        ));

        return Command::SUCCESS;
    }

    private function ensureCalendar(SymfonyStyle $io): int
    {
        foreach (['Events', 'PSA Events'] as $title) {
            $calendar = CalendarModel::findOneBy('title', $title);

            if ($calendar !== null) {
                $io->writeln('Using calendar '.$title.' (id '.$calendar->id.').');

                return (int) $calendar->id;
            }
        }

        $calendar = new CalendarModel();
        $calendar->title = 'PSA Events';
        $calendar->tstamp = time();
        $calendar->save();

        $io->writeln('Created calendar PSA Events (id '.$calendar->id.').');

        return (int) $calendar->id;
    }

    private function ensurePage(
        SymfonyStyle $io,
        string $alias,
        string $title,
        bool $protected,
        ?array $groupIds,
    ): int {
        $root = PageModel::findById(1);
        $rootId = $root?->id ?? 1;
        $page = null;

        foreach (PageModel::findBy('alias', $alias) ?? [] as $candidate) {
            if ((int) $candidate->pid === $rootId) {
                $page = $candidate;
                break;
            }
        }

        if ($page !== null) {
            $page->title = $title;
            $page->published = '1';
            $page->protected = $protected ? '1' : '0';
            $page->groups = $groupIds ? serialize($groupIds) : null;
            $page->tstamp = time();
            $page->save();
            $io->writeln('Updated page /'.$alias.' (id '.$page->id.').');

            return (int) $page->id;
        }

        $page = new PageModel();
        $page->pid = $root?->id ?? 1;
        $page->type = 'regular';
        $page->title = $title;
        $page->alias = $alias;
        $page->published = '1';
        $page->protected = $protected ? '1' : '0';
        $page->groups = $groupIds ? serialize($groupIds) : null;
        $page->sorting = $this->nextSorting((int) $page->pid);
        $page->tstamp = time();
        $page->save();

        $io->writeln('Created page /'.$alias.' (id '.$page->id.').');

        return (int) $page->id;
    }

    private function nextSorting(int $pid): int
    {
        $max = 0;

        foreach (PageModel::findBy('pid', $pid) ?? [] as $page) {
            $max = max($max, (int) $page->sorting);
        }

        return $max + 128;
    }

    private function ensureModule(SymfonyStyle $io, string $name, string $type, array $data): int
    {
        $module = ModuleModel::findOneBy('name', $name);

        if ($module === null) {
            $module = new ModuleModel();
            $module->pid = 1;
            $module->name = $name;
            $module->type = $type;
            $module->tstamp = time();
        }

        foreach ($data as $key => $value) {
            $module->$key = $value;
        }

        $module->tstamp = time();
        $module->save();

        $io->writeln('Saved module '.$name.' (id '.$module->id.').');

        return (int) $module->id;
    }

    private function ensureArticleWithModule(
        SymfonyStyle $io,
        int $pageId,
        string $alias,
        string $title,
        int $moduleId,
    ): void {
        $article = null;

        foreach (ArticleModel::findBy('pid', $pageId) ?? [] as $candidate) {
            $article = $candidate;
            break;
        }

        if ($article === null) {
            $article = new ArticleModel();
            $article->pid = $pageId;
            $article->alias = $alias;
            $article->title = $title;
            $article->sorting = 128;
            $article->published = '1';
            $article->tstamp = time();
            $article->save();
            $io->writeln('Created article '.$title.' (id '.$article->id.').');
        } else {
            $article->title = $title;
            $article->alias = $alias;
            $article->published = '1';
            $article->tstamp = time();
            $article->save();
            $io->writeln('Using article '.$title.' (id '.$article->id.').');
        }

        $content = null;

        foreach (ContentModel::findBy('pid', $article->id) ?? [] as $candidate) {
            if ($candidate->type === 'module') {
                $content = $candidate;
                break;
            }
        }

        if ($content === null) {
            $content = new ContentModel();
            $content->pid = $article->id;
            $content->ptable = 'tl_article';
            $content->type = 'module';
            $content->sorting = 128;
            $content->published = '1';
            $content->tstamp = time();
        }

        $content->module = $moduleId;
        $content->tstamp = time();
        $content->save();

        $io->writeln('Linked module '.$moduleId.' to article '.$article->id.'.');
    }

    /**
     * Keep only the event list on /events. Header, footer, and reader must not
     * live in the page article (layout + list reader setting handle those).
     */
    private function cleanupEventsPage(
        SymfonyStyle $io,
        int $pageId,
        int $listModuleId,
        int $readerModuleId,
    ): void {
        $listModule = ModuleModel::findById($listModuleId);

        if ($listModule !== null) {
            $listModule->cal_readerModule = $readerModuleId;
            $listModule->tstamp = time();
            $listModule->save();
            $io->writeln('Event list module '.$listModuleId.' reader set to '.$readerModuleId.'.');
        }

        $hasListElement = false;

        foreach (ArticleModel::findBy('pid', $pageId) ?? [] as $article) {
            foreach (ContentModel::findBy('pid', $article->id) ?? [] as $content) {
                if ($content->type !== 'module') {
                    continue;
                }

                $moduleId = (int) $content->module;

                if ($moduleId <= 0) {
                    $io->writeln('Removed empty module content element (id '.$content->id.').');
                    $content->delete();

                    continue;
                }

                $module = ModuleModel::findById($moduleId);

                if ($module === null) {
                    $io->writeln('Removed broken module reference (content id '.$content->id.').');
                    $content->delete();

                    continue;
                }

                if (\in_array($module->type, ['html_header', 'html_footer', 'navigation'], true)) {
                    $io->writeln('Removed '.$module->name.' from events page — header/footer belong in the page layout only.');
                    $content->delete();

                    continue;
                }

                if ($module->type === 'eventreader') {
                    $io->writeln('Removed '.$module->name.' from events page — link the reader on the event list module instead.');
                    $content->delete();

                    continue;
                }

                if ($module->type === 'eventlist') {
                    if ($hasListElement && $moduleId !== $listModuleId) {
                        $io->writeln('Removed duplicate event list content element (id '.$content->id.').');
                        $content->delete();

                        continue;
                    }

                    $hasListElement = true;
                    $content->module = $listModuleId;
                    $content->tstamp = time();
                    $content->save();

                    continue;
                }

                $io->writeln('Removed '.$module->name.' ('.$module->type.') from events page — only the event list belongs here.');
                $content->delete();
            }
        }

        if (!$hasListElement) {
            $this->ensureArticleWithModule($io, $pageId, 'events', 'Events', $listModuleId);
        }

        $this->removeDuplicateArticles($io, $pageId, $listModuleId);
    }

    private function removeDuplicateArticles(SymfonyStyle $io, int $pageId, int $listModuleId): void
    {
        $articles = iterator_to_array(ArticleModel::findBy('pid', $pageId) ?? []);
        $keeper = null;

        foreach ($articles as $article) {
            foreach (ContentModel::findBy('pid', $article->id) ?? [] as $content) {
                if ($content->type === 'module' && (int) $content->module === $listModuleId) {
                    $keeper ??= $article;
                    break;
                }
            }
        }

        $keeper ??= $articles[0] ?? null;

        if ($keeper === null) {
            return;
        }

        foreach ($articles as $article) {
            if ((int) $article->id === (int) $keeper->id) {
                continue;
            }

            foreach (ContentModel::findBy('pid', $article->id) ?? [] as $content) {
                $content->delete();
            }

            $io->writeln('Removed duplicate article '.$article->title.' (id '.$article->id.').');
            $article->delete();
        }
    }
}
