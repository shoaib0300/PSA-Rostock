<?php

declare(strict_types=1);

use Contao\Backend;
use Contao\DataContainer;
use Contao\Date;
use Contao\MemberModel;

$strTable = 'tl_psa_meetup_comment';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'ptable' => 'tl_psa_meetup',
        'dataContainer' => Contao\DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'member_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['tstamp'],
            'headerFields' => ['title', 'member_id'],
            'flag' => 6,
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['member_id', 'tstamp', 'comment'],
            'format' => '%s',
            'showColumns' => false,
            'label_callback' => [$strTable, 'generateLabel'],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? 'Delete?').'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{comment_legend},member_id,comment,published',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_psa_meetup.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy', 'table' => 'tl_psa_meetup', 'field' => 'id'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['member_id'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_member.username',
            'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'comment' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['comment'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
            'search' => true,
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['published'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
    ],
];

class tl_psa_meetup_comment extends Backend
{
    public function generateLabel(array $row, string $label, DataContainer $dc, array $args = []): string
    {
        $author = '—';

        if ((int) $row['member_id'] > 0) {
            $member = MemberModel::findById((int) $row['member_id']);

            if ($member !== null) {
                $author = $member->username ?: trim($member->firstname.' '.$member->lastname) ?: 'Member #'.$member->id;
            }
        }

        $text = trim((string) ($row['comment'] ?? ''));
        $preview = mb_strlen($text) > 80 ? mb_substr($text, 0, 80).'…' : $text;

        return sprintf(
            '<strong>%s</strong> <span style="color:#999;padding:0 6px">|</span> %s <span style="color:#999;padding:0 6px">|</span> %s',
            htmlspecialchars($author, ENT_QUOTES),
            Date::parse('d.m.Y H:i', (int) ($row['tstamp'] ?? 0)),
            htmlspecialchars($preview, ENT_QUOTES),
        );
    }
}
