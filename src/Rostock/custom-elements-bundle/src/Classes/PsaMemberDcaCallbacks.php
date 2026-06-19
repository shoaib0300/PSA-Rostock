<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\StringUtil;
use Contao\Validator;

final class PsaMemberDcaCallbacks
{
    public static function storeAvatarUuid(mixed $value, mixed $context = null, mixed $module = null): mixed
    {
        if (\is_array($value)) {
            $uuid = $value['uuid'] ?? null;

            if (!$uuid) {
                return null;
            }

            PsaMemberAvatarProcessor::normalize((string) $uuid);

            return StringUtil::uuidToBin((string) $uuid);
        }

        if (\is_string($value) && $value !== '') {
            if (Validator::isStringUuid($value)) {
                PsaMemberAvatarProcessor::normalize($value);

                return StringUtil::uuidToBin($value);
            }

            return $value;
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
