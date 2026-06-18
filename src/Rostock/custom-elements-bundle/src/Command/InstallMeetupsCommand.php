<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
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
    name: 'psa:install-meetups',
    description: 'Creates the /meetups page and PSA meetup frontend module.',
)]
class InstallMeetupsCommand extends Command
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);

        $pageId = $this->ensurePage($io, 'meetups', 'Meetups', false, null);
        $moduleId = $this->ensureModule($io, 'PSA Meetups', 'psa_meetup', []);
        $this->ensureArticleWithModule($io, $pageId, 'meetups', 'Meetups', $moduleId);

        $io->success(sprintf(
            'Meetups setup ready. Page: /meetups (%d), module id: %d. Run `php vendor/bin/contao-console contao:migrate` if meetup tables are missing.',
            $pageId,
            $moduleId,
        ));

        return Command::SUCCESS;
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
}
