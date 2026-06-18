<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Classes;

use Contao\Config;
use Contao\Date;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\Validator;
use Contao\System;

final class PsaMemberAccountPresenter
{
    /**
     * @param array<string, array<string, string>> $categories
     *
     * @return list<array{legend: string, hint: string, fields: list<array<string, mixed>>}>
     */
    public static function buildGroups(array $categories, string $profileLegend, string $profileHint): array
    {
        System::loadLanguageFile('tl_member');
        $user = FrontendUser::getInstance();
        $groups = [];

        foreach ($categories as $legend => $fieldHtmlMap) {
            if (!\is_array($fieldHtmlMap)) {
                continue;
            }

            $fields = [];

            foreach ($fieldHtmlMap as $fieldName => $widgetHtml) {
                if (!\is_string($fieldName) || !\is_string($widgetHtml)) {
                    continue;
                }

                $fields[] = self::presentField($fieldName, $widgetHtml, $user);
            }

            if ($fields === []) {
                continue;
            }

            $groups[] = [
                'legend' => (string) $legend,
                'hint' => ($legend === $profileLegend || str_contains((string) $legend, 'Community')) ? $profileHint : '',
                'fields' => $fields,
            ];
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    private static function presentField(string $fieldName, string $widgetHtml, FrontendUser $user): array
    {
        $dca = $GLOBALS['TL_DCA']['tl_member']['fields'][$fieldName] ?? [];
        $label = $dca['label'][0] ?? $fieldName;
        $rawValue = $user->$fieldName ?? '';
        $isEmpty = self::isEmptyValue($rawValue);
        $mandatory = self::isMandatoryInDca($fieldName, $dca);

        return [
            'name' => $fieldName,
            'label' => $label,
            'widgetHtml' => $widgetHtml,
            'displayValue' => self::formatDisplayValue($fieldName, $rawValue, $dca),
            'displayHtml' => self::formatDisplayHtml($fieldName, $rawValue, $dca),
            'submitValue' => self::formatSubmitValue($fieldName, $rawValue, $dca),
            'required' => $mandatory,
            'isEmpty' => $isEmpty,
            'showRequiredBadge' => $mandatory && $isEmpty,
        ];
    }

    private static function isEmptyValue(mixed $value): bool
    {
        if (\is_array($value)) {
            return $value === [];
        }

        return trim((string) $value) === '';
    }

    /**
     * @param array<string, mixed> $dca
     */
    private static function isMandatoryInDca(string $fieldName, array $dca): bool
    {
        if ($fieldName === 'password') {
            return false;
        }

        return (bool) ($dca['eval']['mandatory'] ?? false);
    }

    /**
     * @param array<string, mixed> $dca
     */
    private static function formatDisplayValue(string $fieldName, mixed $value, array $dca): string
    {
        if ($fieldName === 'password') {
            return $GLOBALS['TL_LANG']['PSA']['account_password_masked'] ?? '••••••••';
        }

        if ($fieldName === 'avatar') {
            return self::isEmptyValue($value)
                ? ($GLOBALS['TL_LANG']['PSA']['account_empty_value'] ?? '—')
                : ($GLOBALS['TL_LANG']['PSA']['account_avatar_set'] ?? 'Photo uploaded');
        }

        if ($fieldName === 'dateOfBirth' && !self::isEmptyValue($value)) {
            return Date::parse(Config::get('dateFormat'), (int) $value);
        }

        $reference = $dca['reference'] ?? null;

        if (\is_array($reference) && \is_string($value) && $value !== '') {
            return (string) ($reference[$value] ?? $value);
        }

        if (\is_array($value)) {
            return implode(', ', $value);
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : ($GLOBALS['TL_LANG']['PSA']['account_empty_value'] ?? '—');
    }

    /**
     * @param array<string, mixed> $dca
     */
    private static function formatDisplayHtml(string $fieldName, mixed $value, array $dca): string
    {
        if ($fieldName !== 'avatar' || self::isEmptyValue($value)) {
            return '';
        }

        $uuid = self::resolveAvatarUuid($value);

        if ($uuid === null) {
            return '';
        }

        $file = FilesModel::findByUuid($uuid);

        if ($file === null || $file->path === '') {
            return '';
        }

        $src = htmlspecialchars($file->path, ENT_QUOTES);
        $alt = htmlspecialchars((string) ($GLOBALS['TL_LANG']['tl_member']['avatar'][0] ?? 'Profile photo'), ENT_QUOTES);

        return '<img class="psa-account-field__avatar" src="'.$src.'" alt="'.$alt.'" width="80" height="80" loading="lazy">';
    }

    /**
     * @param array<string, mixed> $dca
     */
    private static function formatSubmitValue(string $fieldName, mixed $value, array $dca): string
    {
        if ($fieldName === 'password' || $fieldName === 'avatar') {
            return '';
        }

        if ($fieldName === 'dateOfBirth' && !self::isEmptyValue($value)) {
            return Date::parse(Config::get('dateFormat'), (int) $value);
        }

        if (\is_array($value)) {
            return implode(',', $value);
        }

        return (string) $value;
    }

    private static function resolveAvatarUuid(mixed $value): ?string
    {
        if (\is_string($value) && $value !== '') {
            if (Validator::isStringUuid($value)) {
                return $value;
            }

            if (\strlen($value) === 16) {
                return StringUtil::binToUuid($value);
            }
        }

        return null;
    }
}
