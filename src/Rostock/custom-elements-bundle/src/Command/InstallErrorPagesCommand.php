<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:install-error-pages',
    description: 'Creates Contao 404, 403, and 503 error pages with PSA styling.',
)]
class InstallErrorPagesCommand extends Command
{
    private const ERROR_PAGES = [
        'error_404' => '404 Not found',
        'error_403' => '403 Forbidden',
        'error_503' => '503 Service unavailable',
    ];

    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);

        $root = PageModel::findById(1);
        $rootId = (int) ($root?->id ?? 1);
        $layoutId = (int) ($root?->layout ?? 1);

        foreach (self::ERROR_PAGES as $type => $title) {
            $pageId = $this->ensureErrorPage($io, $rootId, $layoutId, $type, $title);
            $this->ensureArticleWithElement($io, $pageId, $type, $title);
        }

        $this->writeMaintenanceTemplate($io);

        $io->success('Error pages ready (404, 403, 503). Copy var/maintenance.html.example to var/maintenance.html to enable maintenance mode.');

        return Command::SUCCESS;
    }

    private function ensureErrorPage(
        SymfonyStyle $io,
        int $rootId,
        int $layoutId,
        string $type,
        string $title,
    ): int {
        $page = null;

        foreach (PageModel::findBy('type', $type) ?? [] as $candidate) {
            if ((int) $candidate->pid === $rootId) {
                $page = $candidate;
                break;
            }
        }

        if ($page === null) {
            $page = new PageModel();
            $page->pid = $rootId;
            $page->type = $type;
            $page->sorting = $this->nextSorting($rootId);
            $page->tstamp = time();
        }

        $page->title = $title;
        $page->pageTitle = $title;
        $page->alias = '';
        $page->published = '1';
        $page->includeLayout = '1';
        $page->layout = $layoutId;
        $page->robots = 'noindex,nofollow';
        $page->autoforward = '0';
        $page->tstamp = time();
        $page->save();

        $io->writeln('Saved '.$type.' page (id '.$page->id.').');

        return (int) $page->id;
    }

    private function ensureArticleWithElement(
        SymfonyStyle $io,
        int $pageId,
        string $alias,
        string $title,
    ): void {
        $article = null;

        foreach (ArticleModel::findBy('pid', $pageId) ?? [] as $candidate) {
            $article = $candidate;
            break;
        }

        if ($article === null) {
            $article = new ArticleModel();
            $article->pid = $pageId;
            $article->alias = str_replace('error_', '', $alias);
            $article->title = $title;
            $article->sorting = 128;
            $article->published = '1';
            $article->tstamp = time();
            $article->save();
            $io->writeln('Created article for '.$title.' (id '.$article->id.').');
        }

        $content = null;

        foreach (ContentModel::findBy('pid', $article->id) ?? [] as $candidate) {
            if ($candidate->type === 'psa_error') {
                $content = $candidate;
                break;
            }
        }

        if ($content === null) {
            $content = new ContentModel();
            $content->pid = $article->id;
            $content->ptable = 'tl_article';
            $content->type = 'psa_error';
            $content->sorting = 128;
            $content->published = '1';
            $content->tstamp = time();
        }

        $content->tstamp = time();
        $content->save();

        $io->writeln('Linked PSA error element on article '.$article->id.' (content id '.$content->id.').');
    }

    private function nextSorting(int $pid): int
    {
        $max = 0;

        foreach (PageModel::findBy('pid', $pid) ?? [] as $page) {
            $max = max($max, (int) $page->sorting);
        }

        return $max + 128;
    }

    private function writeMaintenanceTemplate(SymfonyStyle $io): void
    {
        $path = \dirname(__DIR__, 5).'/var/maintenance.html.example';

        if (!is_dir(\dirname($path))) {
            mkdir(\dirname($path), 0775, true);
        }

        $contents = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>PSA Rostock — Maintenance</title>
  <style>
    :root {
      --psa-ink: #222f30;
      --psa-graphite: #4d5757;
      --psa-lime: #cef79e;
      --psa-paper: #ffffff;
      --psa-bone: #f7f7f5;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 2rem 1.25rem;
      font-family: "Aspekta", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--psa-ink);
      background: linear-gradient(180deg, var(--psa-bone), var(--psa-paper));
    }
    .panel {
      width: min(100%, 36rem);
      text-align: center;
    }
    .code {
      margin: 0 0 .75rem;
      font-size: clamp(4rem, 14vw, 7rem);
      font-weight: 700;
      line-height: .9;
      letter-spacing: -.05em;
      color: rgba(34, 47, 48, .12);
    }
    h1 {
      margin: 0 0 1rem;
      font-size: clamp(1.75rem, 4vw, 2.5rem);
      line-height: 1.15;
    }
    p {
      margin: 0;
      font-size: 1.05rem;
      line-height: 1.6;
      color: var(--psa-graphite);
    }
  </style>
</head>
<body>
  <main class="panel">
    <p class="code" aria-hidden="true">503</p>
    <h1>We&rsquo;ll be right back</h1>
    <p>PSA Rostock is undergoing brief maintenance. Please try again in a few minutes.</p>
  </main>
</body>
</html>
HTML;

        file_put_contents($path, $contents);
        $io->writeln('Wrote '.$path);
    }
}
