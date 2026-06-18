<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

/**
 * Registers PSA Hero backend fields after the combined DCA cache is loaded.
 */
#[AsHook('loadDataContainer')]
class PsaHeroDcaListener
{
    public function __invoke(string $table): void
    {
        if ($table !== 'tl_content') {
            return;
        }

        $strName = 'tl_content';

        if (!\in_array('addButton', $GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'] ?? [], true)) {
            $GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'][] = 'addButton';
        }

        $GLOBALS['TL_DCA'][$strName]['palettes']['psa_hero'] = '{psa_overlay_legend},type,headline,subline;{psa_source_legend},multiSRC;{psa_scroll_legend},hero_caption,hero_scroll_items;{button_legend},addButton;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
        $GLOBALS['TL_DCA'][$strName]['subpalettes']['addButton'] = 'button_label,button_link,button_target';

        $GLOBALS['TL_DCA'][$strName]['fields']['hero_scroll_items'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['hero_scroll_items'],
            'exclude' => false,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'tl_class' => 'clr',
                'columnFields' => [
                    'headline' => [
                        'label' => &$GLOBALS['TL_LANG'][$strName]['hero_scroll_headline'],
                        'exclude' => true,
                        'inputType' => 'text',
                        'eval' => [
                            'wrapper_style' => 'width:42%;min-width:18rem',
                            'style' => 'width:100%;box-sizing:border-box',
                        ],
                    ],
                    'text' => [
                        'label' => &$GLOBALS['TL_LANG'][$strName]['hero_scroll_text'],
                        'exclude' => true,
                        'inputType' => 'textarea',
                        'eval' => [
                            'rows' => 3,
                            'wrapper_style' => 'width:50%;min-width:22rem',
                            'style' => 'width:100%;box-sizing:border-box',
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ];

        $GLOBALS['TL_DCA'][$strName]['fields']['subline'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['subline'],
            'exclude' => false,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rows' => 2, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ];

        $GLOBALS['TL_DCA'][$strName]['fields']['addButton'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['addButton'],
            'exclude' => false,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ];

        $GLOBALS['TL_DCA'][$strName]['fields']['button_label'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['button_label'],
            'exclude' => false,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        $GLOBALS['TL_DCA'][$strName]['fields']['button_link'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['button_link'],
            'exclude' => false,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'url',
                'decodeEntities' => true,
                'maxlength' => 2048,
                'dcaPicker' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ];

        $GLOBALS['TL_DCA'][$strName]['fields']['button_target'] = [
            'label' => &$GLOBALS['TL_LANG'][$strName]['button_target'],
            'exclude' => false,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ];
    }
}
