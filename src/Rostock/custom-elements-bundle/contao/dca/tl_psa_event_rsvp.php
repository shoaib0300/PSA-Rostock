<?php

declare(strict_types=1);

$strTable = 'tl_psa_event_rsvp';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'event_id,member_id' => 'unique',
                'event_id' => 'index',
                'member_id' => 'index',
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
            'fields' => ['event_id', 'member_id', 'status'],
            'format' => 'Event %s · member %s · %s',
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{rsvp_legend},event_id,member_id,status',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'event_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['event_id'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['member_id'],
            'inputType' => 'select',
            'foreignKey' => 'tl_member.username',
            'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'status' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['status'],
            'inputType' => 'select',
            'options' => ['yes', 'no'],
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['statusRef'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
    ],
];

$GLOBALS['TL_LANG'][$strTable] = [
    'event_id' => ['Event', 'Calendar event id'],
    'member_id' => ['Member', 'Voting member'],
    'status' => ['Status', 'RSVP answer'],
    'statusRef' => [
        'yes' => 'Coming',
        'no' => 'Not coming',
    ],
];
