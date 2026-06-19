<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\Controller;
use Contao\Environment;
use Contao\PageModel;
use Contao\StringUtil;

final class PsaSeo
{
    public const SITE_NAME = 'PSA Rostock';

    public const TAGLINE = 'Polizeisportabteilung Rostock';

    public const THEME_COLOR = '#222f30';

    public const ASSET_PATH = 'files/favicons';

    /**
     * @return array{
     *     siteName: string,
     *     title: string,
     *     description: string,
     *     url: string,
     *     type: string,
     *     themeColor: string,
     *     faviconSvg: string,
     *     favicon32: string,
     *     favicon16: string,
     *     appleTouchIcon: string,
     *     manifest: string,
     *     ogImage: string,
     *     ogImageWidth: int,
     *     ogImageHeight: int,
     *     twitterCard: string,
     *     jsonLd: string
     * }
     */
    public static function buildForPage(?PageModel $page, string $pageTitle, string $description, ?string $canonical): array
    {
        $title = trim($pageTitle) !== '' ? $pageTitle : self::SITE_NAME;
        $metaDescription = StringUtil::substr(strip_tags($description), 320);

        if ($metaDescription === '') {
            $metaDescription = 'Sport, Gemeinschaft und Events der Polizeisportabteilung Rostock.';
        }

        $url = $canonical ?: (Environment::get('url').Environment::get('requestUri'));
        $ogImage = self::assetUrl('og-image.png');

        return [
            'siteName' => self::SITE_NAME,
            'title' => $title,
            'description' => $metaDescription,
            'url' => $url,
            'type' => 'website',
            'themeColor' => self::THEME_COLOR,
            'faviconSvg' => self::assetUrl('favicon.svg'),
            'favicon32' => self::assetUrl('favicon-32x32.png'),
            'favicon16' => self::assetUrl('favicon-16x16.png'),
            'appleTouchIcon' => self::assetUrl('apple-touch-icon.png'),
            'manifest' => self::assetUrl('site.webmanifest'),
            'ogImage' => $ogImage,
            'ogImageWidth' => 1200,
            'ogImageHeight' => 630,
            'twitterCard' => 'summary_large_image',
            'jsonLd' => self::buildJsonLd($title, $metaDescription, $url, $ogImage),
        ];
    }

    public static function assetUrl(string $file): string
    {
        $path = Controller::addAssetsUrlTo(self::ASSET_PATH.'/'.$file);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Environment::get('url').$path;
    }

    private static function buildJsonLd(string $title, string $description, string $url, string $image): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => Environment::get('url').'/#organization',
                    'name' => self::SITE_NAME,
                    'url' => Environment::get('url').'/',
                    'logo' => self::assetUrl('icon-512.png'),
                    'description' => self::TAGLINE,
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => Environment::get('url').'/#website',
                    'url' => Environment::get('url').'/',
                    'name' => self::SITE_NAME,
                    'description' => $description,
                    'publisher' => ['@id' => Environment::get('url').'/#organization'],
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => $url.'#webpage',
                    'url' => $url,
                    'name' => $title,
                    'description' => $description,
                    'isPartOf' => ['@id' => Environment::get('url').'/#website'],
                    'about' => ['@id' => Environment::get('url').'/#organization'],
                    'image' => $image,
                ],
            ],
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
