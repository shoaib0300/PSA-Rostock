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
    name: 'psa:install-guides',
    description: 'Creates arrival-in-germany guide pages with sample articles.',
)]
class InstallGuidesCommand extends Command
{
    private const HUB_ALIAS = 'arrival-in-germany';

    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing guide content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $rootId = (int) (PageModel::findById(1)?->id ?? 1);

        $parentId = $this->ensureHubPage($io, $rootId, [
            'marker' => 'psa-guide-arrival-hub',
            'headline' => 'What to Do When You Arrive in Germany',
            'text' => '<p>Moving to Germany involves a few essential steps in the first weeks. This guide walks you through the most important tasks — from registering your address to opening a bank account and getting connected.</p>',
            'text2' => '<p>Start with city registration (Anmeldung), then health insurance and your bank account. A SIM card and local transport pass will help you settle in quickly. Browse the topics below for practical tips tailored to newcomers in Rostock and across Germany.</p><ul><li><a href="/arrival-in-germany/city-registration">City registration (Anmeldung)</a></li><li><a href="/arrival-in-germany/health-insurance">Health insurance</a></li><li><a href="/arrival-in-germany/bank-account">Open a bank account</a></li><li><a href="/arrival-in-germany/sim-card">Get a SIM card</a></li><li><a href="/arrival-in-germany/residence-permit">Residence permit</a></li><li><a href="/arrival-in-germany/public-transport">Public transport</a></li></ul>',
        ], $force);

        $guides = [
            'city-registration' => [
                'title' => 'City Registration (Anmeldung)',
                'marker' => 'psa-guide-city-registration',
                'headline' => 'Register Your Address (Anmeldung)',
                'text' => '<p>Within 14 days of moving into your apartment, you must register your address at the local Bürgeramt (citizen\'s office). In Rostock, book an appointment online at <a href="https://www.rostock.de" target="_blank" rel="noopener">rostock.de</a> under Bürgeramt / Anmeldung.</p><p><strong>Bring these documents:</strong></p><ul><li>Valid passport or national ID</li><li>Rental contract (Mietvertrag) or confirmation from your landlord (Wohnungsgeberbestätigung)</li><li>Visa or residence permit (if applicable)</li><li>Completed registration form (Anmeldeformular) — available at the office or online</li></ul>',
                'text2' => '<p>After registration you receive a Meldebescheinigung (registration certificate). You need this document for your bank account, health insurance, residence permit extension, and many other official processes.</p><p><strong>Tip:</strong> Appointment slots fill up quickly, especially at the start of semester. Book as soon as you have your rental contract. PSA members often share current wait times in our community meetups.</p>',
            ],
            'health-insurance' => [
                'title' => 'Health Insurance',
                'marker' => 'psa-guide-health-insurance',
                'headline' => 'Health Insurance in Germany',
                'text' => '<p>Health insurance is mandatory for everyone living in Germany. You cannot register at university or start most jobs without it. There are two main types: public (gesetzliche Krankenversicherung, GKV) and private (private Krankenversicherung, PKV).</p><p><strong>Public insurance (recommended for most newcomers):</strong> AOK, TK (Techniker Krankenkasse), Barmer, DAK, and others. Students under 30 pay a reduced rate (around €120/month). Employees are automatically enrolled through their employer.</p>',
                'text2' => '<p><strong>What to do:</strong></p><ol><li>Choose a public insurer and apply online — TK and AOK have English support</li><li>Receive your insurance certificate (Versicherungsbescheinigung)</li><li>Present it at Anmeldung, university enrollment, or your employer</li></ol><p>If you are job-seeking, you can register with the Agentur für Arbeit and get basic coverage through them until you find employment.</p>',
            ],
            'bank-account' => [
                'title' => 'Open a Bank Account',
                'marker' => 'psa-guide-bank-account',
                'headline' => 'Opening a Bank Account in Germany',
                'text' => '<p>A German bank account is essential for receiving salary, paying rent, and managing daily expenses. Most banks require your Anmeldung (registration certificate) and passport. Some online banks let you start before Anmeldung, but you will need the certificate to complete verification.</p><p><strong>Recommended order for newcomers:</strong></p><ol><li><strong>N26</strong> or <strong>Revolut</strong> — fast online setup, English app, good for the first weeks</li><li><strong>DKB</strong> or <strong>ING</strong> — free current accounts, online-focused, widely accepted</li><li><strong>Sparkasse</strong> or <strong>Volksbank</strong> — local branches in Rostock, helpful for in-person support</li></ol>',
                'text2' => '<p><strong>Documents usually needed:</strong> passport, Meldebescheinigung, visa/residence permit, and sometimes proof of income or enrollment.</p><p><strong>Tip:</strong> Avoid accounts with monthly fees (Kontoführungsgebühren). Many banks offer free Girokonto for students and under-28s. Set up SEPA direct debit for rent and insurance payments — landlords and insurers expect it.</p>',
            ],
            'sim-card' => [
                'title' => 'Get a SIM Card',
                'marker' => 'psa-guide-sim-card',
                'headline' => 'Mobile Phone & SIM Card',
                'text' => '<p>You can buy a prepaid SIM at supermarkets (Aldi Talk, Lidl Connect), electronics stores (MediaMarkt, Saturn), or directly from providers. A German phone number is useful for two-factor authentication, appointment confirmations, and local services.</p><p><strong>Popular prepaid options:</strong></p><ul><li><strong>Aldi Talk</strong> — affordable, sold at Aldi stores</li><li><strong>Lidl Connect</strong> — similar value, available at Lidl</li><li><strong>Lebara / Lycamobile</strong> — good for international calls to Pakistan</li><li><strong>O2 / Telekom / Vodafone prepaid</strong> — stronger network, slightly higher cost</li></ul>',
                'text2' => '<p><strong>Contract vs prepaid:</strong> Prepaid is flexible and needs no Schufa credit check — ideal for your first months. Once you have Anmeldung and a stable address, compare contract deals (Laufzeitvertrag) for better data packages.</p><p><strong>Tip:</strong> Bring your passport when buying a SIM. Some providers require online registration (PostIdent) within a few weeks of activation.</p>',
            ],
            'residence-permit' => [
                'title' => 'Residence Permit',
                'marker' => 'psa-guide-residence-permit',
                'headline' => 'Residence Permit (Aufenthaltstitel)',
                'text' => '<p>Non-EU citizens need a valid residence permit to stay in Germany beyond their visa-free period or visa duration. Apply at the Ausländerbehörde (foreigners\' office) — in Rostock this is part of the Ordnungsamt.</p><p><strong>Common permit types:</strong></p><ul><li><strong>Student visa / permit</strong> — tied to university enrollment and blocked account or scholarship proof</li><li><strong>Work visa / Blue Card</strong> — requires a job contract meeting salary thresholds</li><li><strong>Job seeker</strong> — limited duration for qualified professionals looking for work</li><li><strong>Family reunion</strong> — for spouses and dependents</li></ul>',
                'text2' => '<p><strong>Typical documents:</strong> passport, biometric photos, Anmeldung, health insurance proof, rental contract, employment contract or university enrollment, and financial proof.</p><p><strong>Important:</strong> Book your appointment early — wait times can be several months. Never let your permit expire; apply for extension at least 6–8 weeks before expiry. PSA can connect you with members who have gone through the same process.</p>',
            ],
            'public-transport' => [
                'title' => 'Public Transport',
                'marker' => 'psa-guide-public-transport',
                'headline' => 'Getting Around Rostock',
                'text' => '<p>Rostock has a reliable network of buses, trams (Straßenbahn), and regional trains (S-Bahn). The operator is RSAG. Single tickets, day passes, and monthly subscriptions are available at ticket machines, via the RSAG app, or at sales points.</p><p><strong>Ticket options:</strong></p><ul><li><strong>Single ticket (Einzelfahrschein)</strong> — one trip, valid for one direction</li><li><strong>Day ticket (Tageskarte)</strong> — unlimited travel until 3 a.m. the next day</li><li><strong>Monthly pass (Monatskarte)</strong> — best value if you commute daily</li><li><strong>Semester ticket</strong> — included for university students in Mecklenburg-Vorpommern</li></ul>',
                'text2' => '<p><strong>Deutschlandticket:</strong> The nationwide €49/month public transport pass works on local buses and trains across Germany. Available as a subscription — worthwhile if you travel between cities regularly.</p><p><strong>Tip:</strong> Always carry a valid ticket. Random checks (Kontrolle) are common and fines (€60+) apply without one. Bikes can be taken on trams with a bike ticket during off-peak hours.</p>',
            ],
        ];

        foreach ($guides as $shortAlias => $config) {
            $this->ensureGuidePage($io, $parentId, $shortAlias, $config['title'], [
                'marker' => $config['marker'],
                'headline' => $config['headline'],
                'text' => $config['text'],
                'text2' => $config['text2'],
            ], $force);
        }

        $io->success('Arrival guides ready at /arrival-in-germany. Clear cache if pages do not appear immediately.');

        return Command::SUCCESS;
    }

    /**
     * @param array{marker: string, headline: string, text: string, text2: string} $content
     */
    private function ensureHubPage(SymfonyStyle $io, int $rootId, array $content, bool $force): int
    {
        $page = $this->findPageByAliasAndPid(self::HUB_ALIAS, $rootId);

        if ($page === null) {
            $page = new PageModel();
            $page->pid = $rootId;
            $page->type = 'regular';
            $page->title = 'Arriving in Germany';
            $page->alias = self::HUB_ALIAS;
            $page->published = '1';
            $page->useFolderUrl = '0';
            $page->sorting = $this->nextSorting($rootId);
            $page->tstamp = time();
            $page->save();
            $io->writeln('Created page /'.self::HUB_ALIAS.' (id '.$page->id.').');
        } else {
            $page->title = 'Arriving in Germany';
            $page->published = '1';
            $page->useFolderUrl = '0';
            $page->tstamp = time();
            $page->save();
            $io->writeln('Updated page /'.self::HUB_ALIAS.' (id '.$page->id.').');
        }

        $article = $this->ensureArticle((int) $page->id, self::HUB_ALIAS, 'Arriving in Germany');
        $this->ensureGuideContent($io, (int) $article->id, $content, $force);

        return (int) $page->id;
    }

    /**
     * @param array{marker: string, headline: string, text: string, text2: string} $content
     */
    private function ensureGuidePage(
        SymfonyStyle $io,
        int $parentId,
        string $shortAlias,
        string $title,
        array $content,
        bool $force,
    ): void {
        $folderAlias = self::HUB_ALIAS.'/'.$shortAlias;
        $page = $this->findGuideChildPage($parentId, $shortAlias, $folderAlias);

        if ($page === null) {
            $page = new PageModel();
            $page->pid = $parentId;
            $page->type = 'regular';
            $page->title = $title;
            $page->alias = $folderAlias;
            $page->published = '1';
            $page->useFolderUrl = '1';
            $page->sorting = $this->nextSorting($parentId);
            $page->tstamp = time();
            $page->save();
            $io->writeln('Created page /'.$folderAlias.' (id '.$page->id.').');
        } else {
            $page->title = $title;
            $page->alias = $folderAlias;
            $page->published = '1';
            $page->useFolderUrl = '1';
            $page->tstamp = time();
            $page->save();
            $io->writeln('Updated page /'.$folderAlias.' (id '.$page->id.').');
        }

        $article = $this->ensureArticle((int) $page->id, $shortAlias, $title);
        $this->ensureGuideContent($io, (int) $article->id, $content, $force);
    }

    private function findPageByAliasAndPid(string $alias, int $pid): ?PageModel
    {
        foreach (PageModel::findBy('alias', $alias) ?? [] as $page) {
            if ((int) $page->pid === $pid) {
                return $page;
            }
        }

        return null;
    }

    private function findGuideChildPage(int $parentId, string $shortAlias, string $folderAlias): ?PageModel
    {
        foreach (PageModel::findBy('pid', $parentId) ?? [] as $page) {
            if ((string) $page->alias === $folderAlias || (string) $page->alias === $shortAlias) {
                return $page;
            }
        }

        return null;
    }

    private function nextSorting(int $pid): int
    {
        $max = 0;

        foreach (PageModel::findBy('pid', $pid) ?? [] as $page) {
            $max = max($max, (int) $page->sorting);
        }

        return $max + 128;
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
     * @param array{marker: string, headline: string, text: string, text2: string} $definition
     */
    private function ensureGuideContent(SymfonyStyle $io, int $articleId, array $definition, bool $force): void
    {
        $marker = $definition['marker'];
        $content = $this->findContentByMarker($articleId, $marker);

        if ($content !== null && !$force) {
            return;
        }

        $isNew = $content === null;

        if ($content === null) {
            $content = new ContentModel();
            $content->pid = $articleId;
            $content->ptable = 'tl_article';
            $content->type = 'ce_text_double';
            $content->published = '1';
            $content->sorting = 128;
        }

        $content->headline = serialize(['unit' => 'h1', 'value' => $definition['headline']]);
        $content->text = $definition['text'];
        $content->text2 = $definition['text2'];
        $content->cssID = serialize(['', $marker]);
        $content->tstamp = time();
        $content->save();

        $io->writeln(($isNew ? 'Created' : 'Updated').' content ('.$marker.').');
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
}
