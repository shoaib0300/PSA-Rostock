<?php

declare(strict_types=1);

use Contao\Backend;
use Contao\DataContainer;
use Contao\Database;
use Contao\Date;
use Contao\MemberModel;

$strTable = 'tl_psa_meetup';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'ctable' => ['tl_psa_meetup_comment', 'tl_psa_meetup_join', 'tl_psa_meetup_poll_option'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'member_id' => 'index',
                'meetupDate' => 'index',
                'published' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['tstamp'],
            'flag' => 6,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title', 'member_id', 'postType', 'published'],
            'format' => '%s',
            'showColumns' => false,
            'label_callback' => [$strTable, 'generateLabel'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
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
        'default' => '{meetup_legend},title,member_id,postType,description,meetupDate,location,pollQuestion;{publish_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
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
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy', 'table' => 'tl_member', 'field' => 'id'],
        ],
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
        'meetupDate' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['meetupDate'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'tl_class' => 'w50 wizard'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'location' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['location'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'postType' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['postType'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['meetup', 'post'],
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['postTypeRef'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'meetup'",
        ],
        'pollQuestion' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['pollQuestion'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
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

class tl_psa_meetup extends Backend
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

        $date = (int) ($row['meetupDate'] ?? 0) > 0 ? Date::parse('d.m.Y H:i', (int) $row['meetupDate']) : '—';
        $joinCount = (int) Database::getInstance()->prepare('SELECT COUNT(*) AS c FROM tl_psa_meetup_join WHERE pid=?')->execute((int) $row['id'])->c;
        $commentCount = (int) Database::getInstance()->prepare('SELECT COUNT(*) AS c FROM tl_psa_meetup_comment WHERE pid=?')->execute((int) $row['id'])->c;

        return sprintf(
            '<strong>%s</strong> <span style="color:#999;padding:0 6px">|</span> %s <span style="color:#999;padding:0 6px">|</span> %s <span style="color:#999;padding:0 6px">|</span> %d joining · %d comments',
            htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES),
            htmlspecialchars($author, ENT_QUOTES),
            $date,
            $joinCount,
            $commentCount,
        );
    }
}
