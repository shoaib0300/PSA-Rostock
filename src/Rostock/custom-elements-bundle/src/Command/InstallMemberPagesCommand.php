<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Command;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\MemberGroupModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Rostock\CustomElementsBundle\Classes\PsaMemberAvatarStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psa:install-members',
    description: 'Create PSA member group, registration/login/account pages and modules.',
)]
class InstallMemberPagesCommand extends Command
{
    private const REGISTRATION_FIELDS = [
        'firstname',
        'lastname',
        'dateOfBirth',
        'gender',
        'nationality',
        'email',
        'mobile',
        'cityPakistan',
        'cityGermany',
        'university',
        'familyInRostock',
        'nickname',
        'avatar',
        'username',
        'password',
    ];

    private const ACCOUNT_FIELDS = [
        'firstname',
        'lastname',
        'dateOfBirth',
        'gender',
        'nationality',
        'email',
        'mobile',
        'cityPakistan',
        'cityGermany',
        'university',
        'familyInRostock',
        'nickname',
        'avatar',
        'username',
        'password',
    ];

    public function __construct(private readonly ContaoFramework $framework)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->framework->initialize();

        PsaMemberAvatarStorage::ensureUploadFolder();

        $groupId = $this->ensureMemberGroup($io);
        $registerPageId = $this->ensurePage($io, 'register', 'Join us', false, null);
        $loginPageId = $this->ensurePage($io, 'login', 'Login', false, null);
        $forgotPasswordPageId = $this->ensurePage($io, 'forgot-password', 'Forgot password', false, null);
        $accountPageId = $this->ensurePage($io, 'account', 'My account', true, [$groupId]);

        System::loadLanguageFile('psa_member', 'en');

        $registrationEmailText = trim((string) ($GLOBALS['TL_LANG']['PSA']['registration_email'] ?? ''));
        $passwordResetEmailText = trim((string) ($GLOBALS['TL_LANG']['PSA']['password_reset_email'] ?? ''));

        $registrationModuleId = $this->ensureModule($io, 'PSA Registration', 'registration', [
            'editable' => serialize(self::REGISTRATION_FIELDS),
            'memberTpl' => 'member_grouped',
            'reg_groups' => serialize([$groupId]),
            'reg_allowLogin' => '1',
            'reg_activate' => '1',
            'reg_jumpTo' => $loginPageId,
            'reg_text' => $registrationEmailText !== '' ? $registrationEmailText : null,
            'disableCaptcha' => '1',
            'jumpTo' => $loginPageId,
            'headline' => serialize(['unit' => 'h1', 'value' => 'Create your PSA account']),
        ]);

        $loginModuleId = $this->ensureModule($io, 'PSA Login', 'login', [
            'autologin' => '1',
            'customTpl' => 'mod_login',
            'pwResetPage' => $forgotPasswordPageId,
            'jumpTo' => $accountPageId,
            'headline' => serialize(['unit' => 'h1', 'value' => 'Login']),
        ]);

        $lostPasswordModuleId = $this->ensureModule($io, 'PSA Forgot password', 'lostPassword', [
            'reg_skipName' => '1',
            'disableCaptcha' => '1',
            'customTpl' => 'mod_lostPassword',
            'jumpTo' => $loginPageId,
            'reg_jumpTo' => $loginPageId,
            'reg_password' => $passwordResetEmailText !== '' ? $passwordResetEmailText : null,
            'headline' => serialize(['unit' => 'h1', 'value' => 'Reset your password']),
        ]);

        $accountModuleId = $this->ensureModule($io, 'PSA Account', 'personalData', [
            'editable' => serialize(self::ACCOUNT_FIELDS),
            'memberTpl' => 'member_account',
            'jumpTo' => $accountPageId,
            'headline' => serialize(['unit' => 'h1', 'value' => 'My account']),
        ]);

        $this->ensureArticleWithModule($io, $registerPageId, 'registration', 'Registration', $registrationModuleId);
        $this->ensureArticleWithModule($io, $loginPageId, 'login', 'Login', $loginModuleId);
        $this->ensureArticleWithModule($io, $forgotPasswordPageId, 'forgot-password', 'Forgot password', $lostPasswordModuleId);
        $this->ensureArticleWithModule($io, $accountPageId, 'account', 'Account', $accountModuleId);

        $this->wireHeaderRegisterLink($registerPageId, $io);

        $io->success(sprintf(
            'Member setup ready. Pages: /register (%d), /login (%d), /forgot-password (%d), /account (%d). Group id: %d',
            $registerPageId,
            $loginPageId,
            $forgotPasswordPageId,
            $accountPageId,
            $groupId,
        ));

        return Command::SUCCESS;
    }

    private function ensureMemberGroup(SymfonyStyle $io): int
    {
        $group = MemberGroupModel::findOneBy('name', 'PSA Members');

        if ($group !== null) {
            $io->writeln('Member group already exists (id '.$group->id.').');

            return (int) $group->id;
        }

        $group = new MemberGroupModel();
        $group->name = 'PSA Members';
        $group->tstamp = time();
        $group->save();

        $io->writeln('Created member group PSA Members (id '.$group->id.').');

        return (int) $group->id;
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

        foreach (ArticleModel::findBy('alias', $alias) ?? [] as $candidate) {
            if ((int) $candidate->pid === $pageId) {
                $article = $candidate;
                break;
            }
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

    private function wireHeaderRegisterLink(int $registerPageId, SymfonyStyle $io): void
    {
        $header = ModuleModel::findById(1);

        if ($header === null) {
            $io->warning('Header module (id 1) not found; set jumpTo manually to the register page.');

            return;
        }

        $header->type = 'html_header';
        $header->customTpl = '';
        $header->jumpTo = $registerPageId;
        $header->tstamp = time();
        $header->save();

        $footer = ModuleModel::findById(2);

        if ($footer !== null) {
            $footer->jumpTo = $registerPageId;
            $footer->tstamp = time();
            $footer->save();
        }

        $io->writeln('Header module switched to html_header and Join link points to page id '.$registerPageId.'.');
    }
}
