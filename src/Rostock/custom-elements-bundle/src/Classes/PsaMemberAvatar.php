<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

final class PsaMemberAvatar
{
    public static function resolvePath(mixed $value): ?string
    {
        $relative = self::resolveStoragePath($value);

        if ($relative === null) {
            return null;
        }

        System::getContainer()->get('contao.framework')->initialize();

        return Image::getUrl(ltrim($relative, '/'));
    }

    private static function resolveStoragePath(mixed $value): ?string
    {
        $uuid = self::resolveUuid($value);

        if ($uuid === null) {
            return null;
        }

        $file = FilesModel::findByUuid($uuid);

        if ($file === null || (string) $file->path === '') {
            return null;
        }

        return (string) $file->path;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function resolveFromRow(array $row): ?string
    {
        return self::resolvePath($row['avatar'] ?? null);
    }

    public static function resolveFromUser(FrontendUser $user): ?string
    {
        return self::resolvePath($user->avatar ?? null);
    }

    public static function render(?string $path, string $alt, string $class, int $size = 32): string
    {
        if ($path !== null && $path !== '') {
            return sprintf(
                '<img class="%s" src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async">',
                htmlspecialchars($class, ENT_QUOTES),
                htmlspecialchars($path, ENT_QUOTES),
                htmlspecialchars($alt, ENT_QUOTES),
                $size,
                $size,
            );
        }

        return self::renderFallbackIcon($class, $size);
    }

    public static function renderFallbackIcon(string $class, int $size = 18): string
    {
        $classAttr = $class !== '' ? ' class="'.htmlspecialchars($class, ENT_QUOTES).'"' : '';

        return sprintf(
            '<svg%s width="%d" height="%d" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>',
            $classAttr,
            $size,
            $size,
        );
    }

    private static function resolveUuid(mixed $value): ?string
    {
        if (!\is_string($value) || $value === '') {
            return null;
        }

        if (Validator::isStringUuid($value)) {
            return $value;
        }

        if (\strlen($value) === 16) {
            return StringUtil::binToUuid($value);
        }

        return null;
    }
}
