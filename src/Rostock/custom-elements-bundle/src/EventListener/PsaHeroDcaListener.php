<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;

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

        $GLOBALS['TL_DCA'][$strName]['palettes']['psa_hero'] = '{psa_overlay_legend},type,headline,subline;{psa_source_legend},multiSRC;{psa_content_legend},text;{psa_scroll_legend},hero_caption;{button_legend},addButton;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
        $GLOBALS['TL_DCA'][$strName]['subpalettes']['addButton'] = 'button_label,button_link,button_target';

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

        $GLOBALS['TL_DCA'][$strName]['config']['onload_callback'][] = [$this, 'onLoad'];
    }

    public function onLoad($dc): void
    {
        $type = $dc->activeRecord->type ?? Input::get('type') ?? '';

        if ($type !== 'psa_hero') {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_content']['fields']['text']['eval']['mandatory']);

        $isGerman = ($GLOBALS['TL_LANGUAGE'] ?? 'en') === 'de';
        $GLOBALS['TL_LANG']['tl_content']['text'][1] = $isGerman
            ? 'Ein Absatz = Fließtext unter dem Hero. Mehrere h3+p-Paare = Scroll-Schritte mit Überschrift und Text.'
            : 'One paragraph = body text below the hero. Multiple h3+p pairs = scroll steps with headline and text.';
    }
}
