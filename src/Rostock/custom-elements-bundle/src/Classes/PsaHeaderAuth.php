<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

final class PsaHeaderAuth
{
    private const DEFAULT_NAV = [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Events', 'href' => '/events'],
        ['label' => 'Meetups', 'href' => '/meetups'],
        ['label' => 'Team', 'href' => '/team'],
        ['label' => 'Vote', 'href' => '/vote'],
        ['label' => 'About', 'href' => '/about'],
    ];

    private const DEFAULT_ADDITIONAL_LINKS = [
        'title' => 'Arriving in Germany',
        'items' => [
            ['label' => 'Overview', 'parent' => 'arrival-in-germany'],
            ['label' => 'City registration', 'parent' => 'arrival-in-germany', 'alias' => 'city-registration'],
            ['label' => 'Health insurance', 'parent' => 'arrival-in-germany', 'alias' => 'health-insurance'],
            ['label' => 'Bank account', 'parent' => 'arrival-in-germany', 'alias' => 'bank-account'],
            ['label' => 'SIM card', 'parent' => 'arrival-in-germany', 'alias' => 'sim-card'],
            ['label' => 'Residence permit', 'parent' => 'arrival-in-germany', 'alias' => 'residence-permit'],
            ['label' => 'Public transport', 'parent' => 'arrival-in-germany', 'alias' => 'public-transport'],
        ],
    ];

    /**
     * @return array{
     *     isLoggedIn: bool,
     *     memberDisplayName: string,
     *     memberAvatarUrl: string,
     *     accountUrl: string,
     *     loginUrl: string,
     *     logoutUrl: string,
     *     navItems: list<array{label: string, href: string, active: bool}>,
     *     additionalLinks: array{title: string, items: list<array{label: string, href: string, active: bool}>},
     *     memberStats: array{active: int, inRostock: int}
     * }
     */
    public static function resolve(?string $currentPath = null): array
    {
        System::getContainer()->get('contao.framework')->initialize();

        $currentPath ??= rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';

        /** @var TokenChecker $tokenChecker */
        $tokenChecker = System::getContainer()->get('contao.security.token_checker');
        $isLoggedIn = $tokenChecker->hasFrontendUser();
        $accountUrl = self::getPageUrl('account');
        $loginUrl = self::getPageUrl('login');
        $memberDisplayName = '';
        $memberAvatarUrl = '';

        if ($isLoggedIn && ($username = $tokenChecker->getFrontendUsername())) {
            $user = FrontendUser::loadUserByIdentifier($username);

            if ($user instanceof FrontendUser) {
                $nickname = trim((string) ($user->nickname ?? ''));
                $fullName = trim((string) ($user->firstname ?? '').' '.(string) ($user->lastname ?? ''));
                $memberDisplayName = $nickname !== '' ? $nickname : ($fullName !== '' ? $fullName : (string) $user->username);
                $memberAvatarUrl = PsaMemberAvatar::resolveFromUser($user) ?? '';
            }
        }

        $logoutUrl = '';

        if ($isLoggedIn) {
            /** @var LogoutUrlGenerator $logoutUrlGenerator */
            $logoutUrlGenerator = System::getContainer()->get('security.logout_url_generator');
            $logoutUrl = $logoutUrlGenerator->getLogoutPath();
        }

        $navItems = [];

        foreach (self::DEFAULT_NAV as $item) {
            $hrefPath = rtrim($item['href'], '/') ?: '/';
            $navItems[] = [
                'label' => $item['label'],
                'href' => $item['href'],
                'active' => $hrefPath === $currentPath,
            ];
        }

        if (!$isLoggedIn) {
            $loginPath = rtrim($loginUrl, '/') ?: '/';
            $navItems[] = [
                'label' => 'Login',
                'href' => $loginUrl,
                'active' => $loginPath === $currentPath,
            ];
        }

        $additionalItems = [];

        foreach (self::DEFAULT_ADDITIONAL_LINKS['items'] as $item) {
            $href = self::getNestedPageUrl(
                (string) $item['parent'],
                isset($item['alias']) ? (string) $item['alias'] : null,
            );
            $hrefPath = rtrim($href, '/') ?: '/';
            $additionalItems[] = [
                'label' => $item['label'],
                'href' => $href,
                'active' => $hrefPath === $currentPath,
            ];
        }

        return [
            'isLoggedIn' => $isLoggedIn,
            'memberDisplayName' => $memberDisplayName,
            'memberAvatarUrl' => $memberAvatarUrl,
            'accountUrl' => $accountUrl,
            'loginUrl' => $loginUrl,
            'logoutUrl' => $logoutUrl,
            'navItems' => $navItems,
            'additionalLinks' => [
                'title' => self::DEFAULT_ADDITIONAL_LINKS['title'],
                'items' => $additionalItems,
            ],
            'memberStats' => PsaMemberStats::resolve(),
        ];
    }

    public static function getPageUrl(string $alias): string
    {
        $root = PageModel::findById(1);
        $rootId = $root?->id ?? 1;

        foreach (PageModel::findBy('alias', $alias) ?? [] as $page) {
            if ((int) $page->pid === $rootId) {
                return self::generatePageUrl($page);
            }
        }

        return '/'.$alias;
    }

    public static function getNestedPageUrl(string $parentAlias, ?string $childAlias = null): string
    {
        $root = PageModel::findById(1);
        $rootId = $root?->id ?? 1;
        $parent = null;

        foreach (PageModel::findBy('alias', $parentAlias) ?? [] as $page) {
            if ((int) $page->pid === $rootId) {
                $parent = $page;
                break;
            }
        }

        if ($parent === null) {
            return '/'.$parentAlias.($childAlias ? '/'.$childAlias : '');
        }

        if ($childAlias === null) {
            return self::generatePageUrl($parent);
        }

        foreach (PageModel::findBy('alias', $childAlias) ?? [] as $page) {
            if ((int) $page->pid === (int) $parent->id) {
                return self::generatePageUrl($page);
            }
        }

        $folderAlias = $parentAlias.'/'.$childAlias;

        foreach (PageModel::findBy('alias', $folderAlias) ?? [] as $page) {
            if ((int) $page->pid === (int) $parent->id) {
                return self::generatePageUrl($page);
            }
        }

        return '/'.$parentAlias.'/'.$childAlias;
    }

    private static function generatePageUrl(PageModel $page): string
    {
        /** @var ContentUrlGenerator $urlGenerator */
        $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');

        return $urlGenerator->generate($page);
    }
}
