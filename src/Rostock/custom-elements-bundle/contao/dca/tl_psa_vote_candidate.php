<?php

declare(strict_types=1);

use Contao\DataContainer;

$strTable = 'tl_psa_vote_candidate';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'ptable' => 'tl_psa_vote_campaign',
        'dataContainer' => Contao\DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'reason_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'headerFields' => ['title'],
            'flag' => 11,
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['name', 'position'],
            'format' => '%s',
            'label_callback' => [$strTable, 'generateLabel'],
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'cut' => ['href' => 'act=paste&amp;mode=cut', 'icon' => 'cut.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{candidate_legend},reason_id,name,photo,position,description;{publish_legend},published',
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'pid' => [
            'foreignKey' => 'tl_psa_vote_campaign.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'sorting' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'reason_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['reason_id'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_psa_vote_reason.title',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy', 'table' => 'tl_psa_vote_reason', 'field' => 'id'],
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['name'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'photo' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['photo'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'filesOnly' => true,
                'fieldType' => 'radio',
                'extensions' => 'jpg,jpeg,png,webp,svg',
                'tl_class' => 'clr',
            ],
            'sql' => 'binary(16) NULL',
        ],
        'position' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['position'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['description'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['published'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
            'toggle' => true,
        ],
    ],
];

class tl_psa_vote_candidate
{
    /**
     * @param array<string, mixed> $row
     * @param array<int, mixed>   $args
     */
    public function generateLabel(array $row, string $label, DataContainer $dc, array $args = []): string
    {
        $position = trim((string) ($row['position'] ?? ''));

        if ($position === '' && (int) ($row['reason_id'] ?? 0) > 0) {
            $reason = \Contao\Database::getInstance()
                ->prepare('SELECT title FROM tl_psa_vote_reason WHERE id=?')
                ->execute((int) $row['reason_id']);

            if ($reason->numRows > 0) {
                $position = (string) $reason->title;
            }
        }

        if ($position === '') {
            $position = $GLOBALS['TL_LANG']['tl_psa_vote_candidate']['no_position'] ?? 'General';
        }

        return sprintf(
            '%s <span style="color:#999;padding:0 6px">|</span> %s',
            htmlspecialchars((string) $row['name'], ENT_QUOTES),
            htmlspecialchars($position, ENT_QUOTES),
        );
    }
}
