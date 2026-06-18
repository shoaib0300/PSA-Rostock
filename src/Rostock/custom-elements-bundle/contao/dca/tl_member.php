<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Rostock\CustomElementsBundle\Classes\PsaMemberDcaCallbacks;

$GLOBALS['TL_DCA']['tl_member']['fields']['cityPakistan'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['cityPakistan'],
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'mandatory' => true,
        'feEditable' => true,
        'feGroup' => 'address',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
    'search' => true,
];

$GLOBALS['TL_DCA']['tl_member']['fields']['cityGermany'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['cityGermany'],
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'mandatory' => true,
        'feEditable' => true,
        'feGroup' => 'address',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
    'search' => true,
];

$GLOBALS['TL_DCA']['tl_member']['fields']['nationality'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['nationality'],
    'inputType' => 'select',
    'options' => ['pakistan', 'germany', 'dual'],
    'reference' => &$GLOBALS['TL_LANG']['tl_member']['nationalityOptions'],
    'eval' => [
        'mandatory' => true,
        'feEditable' => true,
        'feGroup' => 'personal',
        'tl_class' => 'w50',
        'includeBlankOption' => true,
    ],
    'sql' => "varchar(16) NOT NULL default ''",
    'filter' => true,
];

$GLOBALS['TL_DCA']['tl_member']['fields']['university'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['university'],
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'feEditable' => true,
        'feGroup' => 'profile',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
    'search' => true,
];

$GLOBALS['TL_DCA']['tl_member']['fields']['familyInRostock'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['familyInRostock'],
    'inputType' => 'select',
    'options' => ['yes', 'no'],
    'reference' => &$GLOBALS['TL_LANG']['tl_member']['familyInRostockOptions'],
    'eval' => [
        'feEditable' => true,
        'feGroup' => 'profile',
        'tl_class' => 'w50',
        'includeBlankOption' => true,
    ],
    'sql' => "varchar(3) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['nickname'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['nickname'],
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 64,
        'feEditable' => true,
        'feGroup' => 'profile',
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(64) NOT NULL default ''",
    'search' => true,
];

$GLOBALS['TL_DCA']['tl_member']['fields']['avatar'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['avatar'],
    'inputType' => 'upload',
    'eval' => [
        'extensions' => 'jpg,jpeg,png,webp',
        'feEditable' => true,
        'feGroup' => 'profile',
        'tl_class' => 'w50',
    ],
    'load_callback' => [
        [PsaMemberDcaCallbacks::class, 'loadAvatarUuid'],
    ],
    'save_callback' => [
        [PsaMemberDcaCallbacks::class, 'storeAvatarUuid'],
    ],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_member']['fields']['mobile']['eval']['mandatory'] = true;

PaletteManipulator::create()
    ->addField('cityPakistan', 'city', PaletteManipulator::POSITION_AFTER)
    ->addField('cityGermany', 'cityPakistan', PaletteManipulator::POSITION_AFTER)
    ->addField('nationality', 'dateOfBirth', PaletteManipulator::POSITION_AFTER)
    ->addField('university', 'country', PaletteManipulator::POSITION_AFTER)
    ->addField('familyInRostock', 'university', PaletteManipulator::POSITION_AFTER)
    ->addField('nickname', 'familyInRostock', PaletteManipulator::POSITION_AFTER)
    ->addField('avatar', 'nickname', PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('default', 'tl_member');
