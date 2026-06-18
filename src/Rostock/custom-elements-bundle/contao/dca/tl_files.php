<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Rostock\CustomElementsBundle\Classes\FilesCopyright;

$strTable = 'tl_files';

PaletteManipulator::create()
    ->addField('copyrightPosition','copyright', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default',$strTable);

$GLOBALS['TL_DCA'][$strTable]['fields']['copyrightPosition'] = array(
    'label' => ["Copyright Position", "Wählen sie aus, wo die Copyright Information angezeigt werden soll."],
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'top-left' => "Oben links",
        'top-right' => "Oben rechts",
        'bottom-left' => "Unten links",
        "bottom-right" => "Unten rechts"
        ],
    'eval' => array('maxlength' => 255, 'allowHtml' => false, 'tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default 'top-left'"
);

$GLOBALS['TL_DCA']['tl_files']['palettes']['default'] .= ';{copyright_legend},copyrightStockPhotographyGroup,copyright';

$GLOBALS['TL_DCA']['tl_files']['fields']['copyright'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_files']['copyright'],
    'inputType' => 'select',
    'options' => FilesCopyright::copyrightOptions(),
    'load_callback' => [[FilesCopyright::class, 'copyrightLoadCallback']],
    'eval' => array(
        'maxlength' => 255,
        'tl_class' => 'w50',
        'chosen' => true,
        'includeBlankOption' => true,
    ),
    'sql' => "varchar(255) NOT NULL default ''"
);

if (isset($GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'])) {
    $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'] = array_merge(
        $GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'],
        [
            'title'        => ['maxlength' => 255],
            'alt'          => ['maxlength' => 255],
            'link'         => ['maxlength' => 255],
            'target'       => ['maxlength' => 1],
            'link_text'    => ['maxlength' => 255],
            'caption'      => ['maxlength' => 255],
            'video_teaser' => ['maxlength' => 255],
            'video'        => ['maxlength' => 255],
        ]
    );
}

$GLOBALS['TL_DCA']['tl_files']['fields']['copyrightStockPhotographyGroup'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_files']['copyrightStockPhotographyGroup'],
    'inputType' => 'select',
    'options' => FilesCopyright::stockPhotographyGroupOptions(),
    'reference' => FilesCopyright::stockPhotographyGroupOptionsReference(),
    'eval' => array(
        'tl_class' => 'w50',
        'includeBlankOption' => true
    ),
    'sql' => "int(10) unsigned NULL"
);

