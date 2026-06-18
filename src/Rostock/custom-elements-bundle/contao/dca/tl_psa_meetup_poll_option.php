<?php

declare(strict_types=1);

$strTable = 'tl_psa_meetup_poll_option';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'ptable' => 'tl_psa_meetup',
        'dataContainer' => Contao\DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'headerFields' => ['title', 'member_id'],
            'flag' => 11,
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['label'],
            'format' => '%s',
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{option_legend},label,sorting',
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'pid' => [
            'foreignKey' => 'tl_psa_meetup.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'label' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['label'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

$GLOBALS['TL_LANG'][$strTable]['label'] = ['Option', 'Poll answer option'];
