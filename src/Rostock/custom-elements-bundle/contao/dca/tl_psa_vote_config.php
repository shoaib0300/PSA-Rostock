<?php

declare(strict_types=1);

$strTable = 'tl_psa_vote_config';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'closed' => true,
        'notCreatable' => true,
        'notDeletable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['id'],
            'flag' => 1,
        ],
        'label' => [
            'fields' => ['showTicker', 'tickerPages'],
            'label_callback' => ['tl_psa_vote_config', 'generateLabel'],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{vote_ticker_legend},showTicker,tickerPages',
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'showTicker' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['showTicker'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => '1',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'tickerPages' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['tickerPages'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'eval' => [
                'multiple' => true,
                'fieldType' => 'checkbox',
                'tl_class' => 'clr',
            ],
            'sql' => 'blob NULL',
        ],
    ],
];

class tl_psa_vote_config extends Contao\Backend
{
  /**
   * @param array<string, mixed> $row
   * @param array<int, mixed>    $args
   */
  public function generateLabel(array $row, string $label, Contao\DataContainer $dc, array $args = []): string
  {
    $title = $GLOBALS['TL_LANG']['MOD']['psa_vote_settings'][0] ?? 'Vote settings';

    if (($row['showTicker'] ?? '') !== '1') {
      return $title.' ('.($GLOBALS['TL_LANG']['tl_psa_vote_config']['ticker_disabled'] ?? 'disabled').')';
    }

    $pages = Contao\StringUtil::deserialize($row['tickerPages'] ?? null, true);

    if (\is_array($pages) && $pages !== []) {
      return $title.' ('.sprintf($GLOBALS['TL_LANG']['tl_psa_vote_config']['ticker_page_count'] ?? '%d pages', \count($pages)).')';
    }

    return $title.' ('.($GLOBALS['TL_LANG']['tl_psa_vote_config']['ticker_all_pages'] ?? 'all pages').')';
  }
}
