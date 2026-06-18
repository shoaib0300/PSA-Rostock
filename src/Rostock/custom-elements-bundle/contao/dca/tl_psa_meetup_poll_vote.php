<?php

declare(strict_types=1);

$strTable = 'tl_psa_meetup_poll_vote';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'meetup_id,member_id' => 'unique',
                'meetup_id' => 'index',
                'option_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['tstamp'],
            'flag' => 6,
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['meetup_id', 'option_id', 'member_id'],
            'format' => 'Meetup %s · option %s · member %s',
        ],
        'operations' => [
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{vote_legend},meetup_id,option_id,member_id',
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'meetup_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['meetup_id'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'option_id' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['option_id'],
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
    ],
];

$GLOBALS['TL_LANG'][$strTable] = [
    'meetup_id' => ['Meetup', 'Meetup post id'],
    'option_id' => ['Option', 'Poll option id'],
    'member_id' => ['Member', 'Voting member'],
];
