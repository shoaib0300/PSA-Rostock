<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\StringUtil;

final class PsaMemberDcaCallbacks
{
    public static function storeAvatarUuid(mixed $value, mixed $context = null, mixed $module = null): mixed
    {
        if (\is_array($value)) {
            $uuid = $value['uuid'] ?? null;

            return $uuid ? StringUtil::uuidToBin($uuid) : null;
        }

        if (\is_string($value) && $value !== '') {
            return StringUtil::isUuid($value) ? StringUtil::uuidToBin($value) : $value;
        }

        return null;
    }

    public static function loadAvatarUuid(mixed $value, mixed $context = null, mixed $module = null): mixed
    {
        if (!\is_string($value) || $value === '') {
            return '';
        }

        return StringUtil::binToUuid($value);
    }
}
