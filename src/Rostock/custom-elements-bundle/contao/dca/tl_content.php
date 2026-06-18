<?php

$strName = 'tl_content';

$GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'][] = 'addButton';

$GLOBALS['TL_DCA'][$strName]['palettes']['ce_text_double'] = '{type_legend},type,headline;{text_legend},text,addButton;
                                                                    {type_legend},text2;{protected_legend:hide},protected;
                                                                    {expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA'][$strName]['palettes']['claim_text_image'] = '{type_legend},type,headline,subline;{text_image_legend},side_text,side_image,claim_image;{button_legend},addButton;{layout_legend},mirror,full_width,v_center,center_text,dark_mode;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA'][$strName]['palettes']['ce_slider_main'] = '{type_legend},type,headline,subline;{text_legend},optional_text;{button_legend},addButton;
                                                                    {source_legend},multiSRC, customImageSize;{slider_legend},slide_count_mobile,slide_count_tablet,slide_count_desktop;
                                                                    {layout_legend},full_width,center_text,dark_mode;{protected_legend:hide},protected;
                                                                    {expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA'][$strName]['palettes']['psa_hero'] = '{psa_overlay_legend},type,headline,subline;{psa_source_legend},multiSRC;{button_legend},addButton;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA'][$strName]['subpalettes']['addButton'] = 'button_label,button_link,button_target';

$GLOBALS['TL_DCA'][$strName]['fields']['button_type'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['button_type'],
    'inputType' => 'select',
    'options' => ['primary', 'secondary'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(32) NOT NULL default 'primary'",
];

$GLOBALS['TL_DCA'][$strName]['fields']['text2'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['text2'],
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA'][$strName]['fields']['backgroundImageSRC'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['backgroundImageSRC'],
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA'][$strName]['fields']['backgroundVideo'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['backgroundVideo'],
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => 'mp4,webm', 'tl_class' => 'w50'],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA'][$strName]['fields']['claim_image'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['claim_image'],
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'w50 widget'],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA'][$strName]['fields']['banner_headline'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['banner_headline'],
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$strName]['fields']['banner_subline'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['banner_subline'],
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$strName]['fields']['banner_link'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['banner_link'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA'][$strName]['fields']['optional_text'] = [
    'label' => &$GLOBALS['TL_LANG'][$strName]['optional_text'],
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
