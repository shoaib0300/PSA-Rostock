<?php

declare(strict_types=1);

$strTable = 'tl_psa_team_member';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'sorting' => 'index',
                'published' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['sorting'],
            'flag' => 11,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['name', 'position', 'email'],
            'format' => '%s <span style="color:#999;padding:0 6px">|</span> %s <span style="color:#999;padding:0 6px">|</span> %s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
            ],
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
        'default' => '{member_legend},name,photo,position,degree,university;{contact_legend},email,phone;{publish_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
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
        'degree' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['degree'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'university' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['university'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'email' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['email'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'email', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'search' => true,
        ],
        'phone' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['phone'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
            'search' => true,
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
