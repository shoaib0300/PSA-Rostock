<?php

$strName = 'tl_content';

$GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'][]      =  'addButton';
$GLOBALS['TL_DCA'][$strName]['palettes']['ce_text_double']      =  '{type_legend},type,headline;{text_legend},text,addButton;
                                                                    {type_legend},text2;{protected_legend:hide},protected;
                                                                    {expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA'][$strName]['palettes']['ce_start_hero']       =  '{type_legend},type;{hero_legend},playerSRC,backgroundImageSRC;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA'][$strName]['palettes']['claim_text_image']    =  '{type_legend},type,headline,subline;{text_image_legend},side_text,side_image,claim_image;{button_legend},addButton;{layout_legend},mirror,full_width,v_center,center_text,dark_mode;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA'][$strName]['palettes']['ce_slider_main']      =  '{type_legend},type,headline,subline;{text_legend},optional_text;{button_legend},addButton;
                                                                    {source_legend},multiSRC, customImageSize;{slider_legend},slide_count_mobile,slide_count_tablet,slide_count_desktop;
                                                                    {layout_legend},full_width,center_text,dark_mode;{protected_legend:hide},protected;
                                                                    {expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA'][$strName]['palettes']['top_hero_header']     =  '{type_legend},type,headline;
                                                                    {source_legend},image_video; 
                                                                    {protected_legend:hide},protected;
                                                                    {expert_legend:hide},cssID;
                                                                    {invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA'][$strName]['palettes']['ce_home_header']          = '{type_legend},type;{information_legend},show_information;
                                                                    {image_legend},backgroundVideo,claim_image_right;{button_legend},addButton;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';


$GLOBALS['TL_DCA'][$strName]['fields']['image_video'] = [
    'label'                 => &$GLOBALS['TL_LANG'][$strName]['image_video'],
    'inputType'             => 'fileTree',
    'eval'                  => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 widget'),
    'sql'                   => "binary(16) NULL"
];


// Second text field
$GLOBALS['TL_DCA'][$strName]['fields']['text2'] = array(
    'label'     => &$GLOBALS['TL_LANG'][$strName]['text2'],
    'inputType' => 'textarea',
    'eval'      => array('rte' => 'tinyMCE', 'helpwizard' => true),
    'sql'       => "text NULL"
);
$GLOBALS['TL_DCA'][$strName]['fields']['backgroundImageSRC'] = array(
    'label'     => &$GLOBALS['TL_LANG'][$strName]['backgroundImageSRC'],
    'inputType'               => 'fileTree',
    'eval'                    => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
    'sql'                     => "binary(16) NULL"
);
$GLOBALS['TL_DCA'][$strName]['fields']['claim_image'] = [
    'label'                 => &$GLOBALS['TL_LANG'][$strName]['claim_image'],
    'inputType'             => 'fileTree',
    'eval'                  => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 widget'),
    'sql'                   => "binary(16) NULL"
];
$GLOBALS['TL_DCA'][$strName]['fields']['backgroundVideo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$strName]['backgroundVideo'],
    'inputType'               => 'fileTree',
    'eval'                  => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 widget'),
    'sql'                     => "binary(16) NULL",
);
$GLOBALS['TL_DCA'][$strName]['fields']['information_headline'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$strName]['information_headline'],
    'inputType'               => 'text',
    'eval'                  => array('tl_class' => 'w50 widget'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);
$GLOBALS['TL_DCA'][$strName]['fields']['claim_image_right'] = array(
    'label'                   => &$GLOBALS['TL_LANG'][$strName]['claim_image_right'],
    'inputType'               => 'fileTree',
    'eval'                  => array('mandatory' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'w50 widget'),
    'sql'                     => "binary(16) NULL",
);

$GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'][] = 'show_information';
$GLOBALS['TL_DCA'][$strName]['subpalettes']['show_information'] = 'information_headline,information_box_text';
$GLOBALS['TL_DCA'][$strName]['fields']['show_information'] = array(
    'label'     => &$GLOBALS['TL_LANG'][$strName]['show_information_box'],
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 widget', 'submitOnChange' => true),
    'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA'][$strName]['fields']['information_box_text'] = array(
    'label'     => &$GLOBALS['TL_LANG'][$strName]['text2'],
    'inputType' => 'textarea',
    'eval'      => array('rte' => 'tinyMCE', 'helpwizard' => true,'tl_class' => 'w50 widget'),
    'sql'       => "text NULL"
);
