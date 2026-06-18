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
        ['label' => 'Contributors', 'href' => '/contributors'],
        ['label' => 'About', 'href' => '/about'],
    ];

    /**
     * @return array{
     *     isLoggedIn: bool,
     *     memberDisplayName: string,
     *     accountUrl: string,
     *     loginUrl: string,
     *     logoutUrl: string,
     *     navItems: list<array{label: string, href: string, active: bool}>
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

        if ($isLoggedIn && ($username = $tokenChecker->getFrontendUsername())) {
            $user = FrontendUser::loadUserByIdentifier($username);

            if ($user instanceof FrontendUser) {
                $nickname = trim((string) ($user->nickname ?? ''));
                $fullName = trim((string) ($user->firstname ?? '').' '.(string) ($user->lastname ?? ''));
                $memberDisplayName = $nickname !== '' ? $nickname : ($fullName !== '' ? $fullName : (string) $user->username);
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

        return [
            'isLoggedIn' => $isLoggedIn,
            'memberDisplayName' => $memberDisplayName,
            'accountUrl' => $accountUrl,
            'loginUrl' => $loginUrl,
            'logoutUrl' => $logoutUrl,
            'navItems' => $navItems,
        ];
    }

    public static function getPageUrl(string $alias): string
    {
        $root = PageModel::findById(1);
        $rootId = $root?->id ?? 1;

        foreach (PageModel::findBy('alias', $alias) ?? [] as $page) {
            if ((int) $page->pid === $rootId) {
                /** @var ContentUrlGenerator $urlGenerator */
                $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');

                return $urlGenerator->generate($page);
            }
        }

        return '/'.$alias;
    }
}
