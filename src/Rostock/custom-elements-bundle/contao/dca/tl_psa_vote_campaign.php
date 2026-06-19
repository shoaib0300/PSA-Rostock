<?php

declare(strict_types=1);

use Contao\Backend;
use Contao\Config;
use Contao\DataContainer;
use Contao\Database;
use Contao\Date;

$strTable = 'tl_psa_vote_campaign';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'ctable' => ['tl_psa_vote_candidate'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'published' => 'index',
                'startDate' => 'index',
                'endDate' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['startDate'],
            'flag' => 8,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title', 'startDate', 'endDate', 'published'],
            'format' => '%s',
            'label_callback' => [$strTable, 'generateLabel'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
            ],
        ],
        'operations' => [
            'edit' => ['href' => 'table=tl_psa_vote_candidate', 'icon' => 'children.svg'],
            'editheader' => ['href' => 'act=edit', 'icon' => 'header.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{campaign_legend},title,description,startDate,endDate,showResults;{publish_legend},published',
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'title' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['title'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['description'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
            'search' => true,
        ],
        'startDate' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['startDate'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'date',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'save_callback' => [
                [$strTable, 'normalizeStartDate'],
            ],
            'load_callback' => [
                [$strTable, 'loadDateField'],
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'endDate' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['endDate'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'date',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'save_callback' => [
                [$strTable, 'normalizeEndDate'],
                [$strTable, 'validateEndDate'],
            ],
            'load_callback' => [
                [$strTable, 'loadDateField'],
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'showResults' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['showResults'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['after_vote', 'after_end', 'always', 'never'],
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['showResultsRef'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'after_vote'",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['published'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '0'",
            'toggle' => true,
        ],
    ],
];

class tl_psa_vote_campaign extends Backend
{
    public function loadDateField(mixed $value, DataContainer $dc): string
    {
        $timestamp = $this->parseDateToTimestamp($value);

        return $timestamp > 0 ? Date::parse(Config::get('dateFormat'), $timestamp) : '';
    }

    public function normalizeStartDate(mixed $value, DataContainer $dc): int
    {
        return $this->parseDateToTimestamp($value);
    }

    public function normalizeEndDate(mixed $value, DataContainer $dc): int
    {
        return $this->parseDateToTimestamp($value, true);
    }

    public function validateEndDate(mixed $value, DataContainer $dc): int
    {
        $end = $this->parseDateToTimestamp($value, true);

        if ($end === 0) {
            return 0;
        }

        $startRaw = \Contao\Input::post('startDate');

        if ($startRaw) {
            $start = $this->parseDateToTimestamp($startRaw);
        } else {
            $start = $this->parseDateToTimestamp($dc->activeRecord->startDate ?? 0);
        }

        if ($start > 0 && $end < $start) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['voteEndBeforeStart'] ?? 'End date must be on or after the start date.');
        }

        return $end;
    }

    private function parseDateToTimestamp(mixed $value, bool $endOfDay = false): int
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return 0;
        }

        if (\is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return 0;
            }
        }

        if (is_numeric($value)) {
            $numeric = (int) $value;

            if ($numeric >= 20000101 && $numeric <= 29991231) {
                $value = sprintf(
                    '%04d-%02d-%02d',
                    intdiv($numeric, 10000),
                    intdiv($numeric % 10000, 100),
                    $numeric % 100,
                );
            } elseif ($numeric > 946684800) {
                $value = date('Y-m-d', $numeric);
            } else {
                return 0;
            }
        }

        $string = trim((string) $value);

        foreach (['Y-m-d', 'd.m.Y', 'd/m/Y', 'm/d/Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $string);

            if ($date instanceof \DateTimeImmutable) {
                return (int) $date->setTime($endOfDay ? 23 : 0, $endOfDay ? 59 : 0, $endOfDay ? 59 : 0)->getTimestamp();
            }
        }

        $parsed = strtotime($string);

        if ($parsed <= 0) {
            return 0;
        }

        $date = (new \DateTimeImmutable('@'.$parsed))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return (int) $date->setTime($endOfDay ? 23 : 0, $endOfDay ? 59 : 0, $endOfDay ? 59 : 0)->getTimestamp();
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, mixed>   $args
     */
    public function generateLabel(array $row, string $label, DataContainer $dc, array $args = []): string
    {
        $status = $this->resolveStatus($row);
        $statusLabel = $GLOBALS['TL_LANG']['tl_psa_vote_campaign']['statusRef'][$status] ?? $status;
        $votes = (int) Database::getInstance()->prepare('SELECT COUNT(*) AS c FROM tl_psa_vote_ballot WHERE campaign_id=?')->execute((int) $row['id'])->c;
        $start = $this->parseDateToTimestamp((int) ($row['startDate'] ?? 0));
        $end = $this->parseDateToTimestamp((int) ($row['endDate'] ?? 0), true);

        $parts = [
            '<strong>'.htmlspecialchars((string) $row['title'], ENT_QUOTES).'</strong>',
            '<span style="color:#999">'.htmlspecialchars($statusLabel, ENT_QUOTES).'</span>',
            sprintf(
                '%s – %s',
                $start > 0 ? Date::parse('d.m.Y', $start) : '—',
                $end > 0 ? Date::parse('d.m.Y', $end) : '—',
            ),
            sprintf($GLOBALS['TL_LANG']['tl_psa_vote_campaign']['ballotCount'] ?? '%d votes', $votes),
        ];

        return implode(' <span style="color:#ccc">|</span> ', $parts);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveStatus(array $row): string
    {
        if (($row['published'] ?? '') !== '1') {
            return 'draft';
        }

        $now = time();
        $start = $this->parseDateToTimestamp((int) ($row['startDate'] ?? 0));
        $end = $this->parseDateToTimestamp((int) ($row['endDate'] ?? 0), true);

        if ($start > 0 && $now < $start) {
            return 'upcoming';
        }

        if ($end > 0 && $now > $end) {
            return 'ended';
        }

        return 'active';
    }
}
