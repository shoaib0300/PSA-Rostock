<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:seed-page-content',
    description: 'Adds or updates sample intro text on PSA frontend pages.',
)]
class SeedPageContentCommand extends Command
{
    private const SAMPLE_CLASS = 'psa-sample-intro';

    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing sample content even if already present');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $pages = [
            'index' => [
                'articleTitle' => 'Home',
                'articleAlias' => 'index',
                'elements' => [
                    [
                        'marker' => 'psa-sample-home',
                        'type' => 'ce_text_double',
                        'sorting' => 256,
                        'headline' => 'A Home Away From Home for Pakistanis in Rostock',
                        'text' => '<p>Welcome to PSA Rostock — a warm community for Pakistanis and friends in our city. Whether you are new here or have lived in Rostock for years, you will find events, meetups, and people ready to connect.</p>',
                        'text2' => '<p>Explore upcoming events, join member meetups, and take part in community decisions. Together we celebrate culture, support one another, and make Rostock feel like home.</p>',
                    ],
                ],
            ],
            'about' => [
                'articleTitle' => 'About',
                'articleAlias' => 'about',
                'elements' => [
                    [
                        'marker' => 'psa-sample-about',
                        'type' => 'ce_text_double',
                        'sorting' => 64,
                        'headline' => 'About PSA Rostock',
                        'text' => '<p>PSA Rostock brings together Pakistanis and friends who want community, culture, and belonging in Mecklenburg-Vorpommern. We organize events, coordinate member meetups, and create welcoming spaces for newcomers and long-time residents alike.</p>',
                        'text2' => '<p>Our work is volunteer-led and open to anyone who shares our spirit of friendship and mutual support. From seasonal celebrations to practical help settling in, PSA is here for you.</p>',
                    ],
                ],
            ],
            'events' => [
                'articleTitle' => 'Events',
                'articleAlias' => 'events',
                'elements' => [
                    [
                        'marker' => 'psa-sample-events-intro',
                        'type' => 'text',
                        'sorting' => 24,
                        'headline' => 'Community Events',
                        'text' => '<p>Browse upcoming gatherings, workshops, and celebrations organized by and for our community. RSVP to let us know you are coming and see who else will be there.</p>',
                    ],
                ],
            ],
            'meetups' => [
                'articleTitle' => 'Meetups',
                'articleAlias' => 'meetups',
                'elements' => [
                    [
                        'marker' => 'psa-sample-meetups-intro',
                        'type' => 'text',
                        'sorting' => 32,
                        'headline' => 'Member Meetups',
                        'text' => '<p>Casual outings planned by members — harbour walks, dinners, games nights, and more. Join an existing meetup or suggest your own idea for the community.</p>',
                    ],
                    [
                        'marker' => 'psa-sample-meetups-col-1',
                        'type' => 'ce_text_double',
                        'sorting' => 256,
                        'headline' => 'Get together, keep it simple',
                        'text' => '<p>Meetups are member-led and informal. Pick a time, choose a place, and invite others along. Perfect for making friends outside of big events.</p>',
                        'text2' => '<p>Popular ideas include coffee meetups, study sessions, sports in the park, and shared meals. If you can host or guide, members will gladly join.</p>',
                    ],
                    [
                        'marker' => 'psa-sample-meetups-col-2',
                        'type' => 'ce_text_double',
                        'sorting' => 384,
                        'headline' => 'Join or create a meetup',
                        'text' => '<p>See what is coming up in the list below. You can join with one click when you are logged in as a member.</p>',
                        'text2' => '<p>Want to lead something new? Create a meetup, add a short description, and optional poll to find the best date with everyone.</p>',
                    ],
                ],
            ],
            'team' => [
                'articleTitle' => 'Team',
                'articleAlias' => 'team',
                'elements' => [
                    [
                        'marker' => 'psa-sample-team-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'Our Team',
                        'text' => '<p>Meet the volunteers who keep PSA Rostock running — from events and communications to membership support and community outreach. Reach out anytime if you would like to help.</p>',
                    ],
                ],
            ],
            'vote' => [
                'articleTitle' => 'Vote',
                'articleAlias' => 'vote',
                'elements' => [
                    [
                        'marker' => 'psa-sample-vote-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'Member Voting',
                        'text' => '<p>Take part in community decisions. Vote for board positions, event leads, and other matters that shape PSA Rostock. Each member can cast their vote once per campaign.</p>',
                    ],
                ],
            ],
            'register' => [
                'articleTitle' => 'Join us',
                'articleAlias' => 'register',
                'elements' => [
                    [
                        'marker' => 'psa-sample-register-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'Join PSA Rostock',
                        'text' => '<p>Create your free member account to RSVP to events, join meetups, vote in community polls, and connect with others. Registration only takes a minute.</p>',
                    ],
                ],
            ],
            'login' => [
                'articleTitle' => 'Login',
                'articleAlias' => 'login',
                'elements' => [
                    [
                        'marker' => 'psa-sample-login-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'Member Login',
                        'text' => '<p>Sign in to access your account, manage your profile, and take part in member-only activities across PSA Rostock.</p>',
                    ],
                ],
            ],
            'account' => [
                'articleTitle' => 'My account',
                'articleAlias' => 'account',
                'elements' => [
                    [
                        'marker' => 'psa-sample-account-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'My Account',
                        'text' => '<p>Update your profile, review your community activity, and manage your membership settings. Your account is your home base in PSA Rostock.</p>',
                    ],
                ],
            ],
            'forgot-password' => [
                'articleTitle' => 'Forgot password',
                'articleAlias' => 'forgot-password',
                'elements' => [
                    [
                        'marker' => 'psa-sample-forgot-intro',
                        'type' => 'text',
                        'sorting' => 64,
                        'headline' => 'Reset Your Password',
                        'text' => '<p>Enter the email address linked to your account and we will send you instructions to choose a new password.</p>',
                    ],
                ],
            ],
            'contributors' => [
                'articleTitle' => 'Contributors',
                'articleAlias' => 'contributors',
                'elements' => [
                    [
                        'marker' => 'psa-sample-contributors',
                        'type' => 'ce_text_double',
                        'sorting' => 128,
                        'headline' => 'Thank You to Our Contributors',
                        'text' => '<p>PSA Rostock is made possible by members who donate time, ideas, and energy. From event planning to social media, every contribution helps our community thrive.</p>',
                        'text2' => '<p>We are grateful to everyone who has hosted a meetup, shared a meal, welcomed a newcomer, or volunteered behind the scenes. This community belongs to all of us.</p>',
                    ],
                ],
            ],
        ];

        $rootId = (int) (PageModel::findById(1)?->id ?? 1);
        $updated = 0;
        $created = 0;
        $skipped = 0;

        foreach ($pages as $alias => $config) {
            $page = $this->findPageByAlias($rootId, $alias);

            if ($page === null) {
                $io->warning('Page /'.$alias.' not found — skipped.');
                ++$skipped;
                continue;
            }

            $article = $this->ensureArticle(
                (int) $page->id,
                (string) $config['articleAlias'],
                (string) $config['articleTitle'],
            );

            foreach ($config['elements'] as $element) {
                $result = $this->ensureSampleElement((int) $article->id, $element, $force);

                if ($result === 'created') {
                    ++$created;
                    $io->writeln('Created sample content on /'.$alias.' ('.$element['marker'].').');
                } elseif ($result === 'updated') {
                    ++$updated;
                    $io->writeln('Updated sample content on /'.$alias.' ('.$element['marker'].').');
                } else {
                    ++$skipped;
                }
            }
        }

        $io->success(sprintf(
            'Page content seed finished. %d created, %d updated, %d skipped.',
            $created,
            $updated,
            $skipped,
        ));

        return Command::SUCCESS;
    }

    private function findPageByAlias(int $rootId, string $alias): ?PageModel
    {
        foreach (PageModel::findBy('alias', $alias) ?? [] as $page) {
            if ((int) $page->pid === $rootId) {
                return $page;
            }
        }

        return null;
    }

    private function ensureArticle(int $pageId, string $alias, string $title): ArticleModel
    {
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
        }

        return $article;
    }

    /**
     * @param array{
     *     marker: string,
     *     type: string,
     *     sorting: int,
     *     headline: string,
     *     text: string,
     *     text2?: string
     * } $definition
     */
    private function ensureSampleElement(int $articleId, array $definition, bool $force): string
    {
        $marker = (string) $definition['marker'];
        $content = $this->findContentByMarker($articleId, $marker);

        if ($content === null) {
            $content = $this->findAdoptableContent($articleId, (string) $definition['type']);
        }

        if ($content !== null && !$force && $this->hasMarker($content)) {
            return 'skipped';
        }

        $isNew = $content === null;

        if ($content === null) {
            $content = new ContentModel();
            $content->pid = $articleId;
            $content->ptable = 'tl_article';
            $content->type = (string) $definition['type'];
            $content->published = '1';
        }

        $content->sorting = (int) $definition['sorting'];
        $content->headline = $this->serializeHeadline((string) $definition['headline']);
        $content->text = (string) $definition['text'];
        $content->cssID = $this->serializeCssId($marker);

        if ($definition['type'] === 'ce_text_double') {
            $content->text2 = (string) ($definition['text2'] ?? '');
        }

        $content->tstamp = time();
        $content->save();

        return $isNew ? 'created' : 'updated';
    }

    private function findContentByMarker(int $articleId, string $marker): ?ContentModel
    {
        foreach (ContentModel::findBy('pid', $articleId) ?? [] as $content) {
            $css = StringUtil::deserialize((string) ($content->cssID ?? ''), true);

            if (!\is_array($css)) {
                continue;
            }

            if (($css[1] ?? '') === $marker || ($css[0] ?? '') === $marker) {
                return $content;
            }
        }

        return null;
    }

    private function findAdoptableContent(int $articleId, string $type): ?ContentModel
    {
        $matches = [];

        foreach (ContentModel::findBy('pid', $articleId) ?? [] as $content) {
            if ((string) $content->type !== $type) {
                continue;
            }

            if ($this->hasMarker($content)) {
                continue;
            }

            $matches[] = $content;
        }

        return $matches[0] ?? null;
    }

    private function hasMarker(ContentModel $content): bool
    {
        $css = StringUtil::deserialize((string) ($content->cssID ?? ''), true);

        if (!\is_array($css)) {
            return false;
        }

        return ($css[1] ?? '') !== '' || ($css[0] ?? '') !== '';
    }

    private function serializeHeadline(string $value, string $unit = 'h2'): string
    {
        return serialize(['unit' => $unit, 'value' => $value]);
    }

    private function serializeCssId(string $marker): string
    {
        return serialize(['', $marker]);
    }
}
