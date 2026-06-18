<?php
// Path: src/Rostock/custom-elements-bundle/contao/dca/tl_quickbooker.php
use Contao\Backend;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\StringUtil;
use Contao\Image;
use Contao\Versions;
use Contao\Database;
use Contao\Controller;

/**
 * Table tl_quickbooker - Seasonal minimum stay configuration
 */
$strTable = 'tl_quickbooker';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'enableVersioning' => true,
        'onsubmit_callback' => [
            ['tl_quickbooker', 'validateNoOverlap'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'startDate,endDate' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['startDate'],
            'flag' => 6,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['seasonName', 'startDate', 'endDate', 'minStay', 'published'],
            'showColumns' => false,
            'label_callback' => [$strTable, 'listSeasons'],
        ],
        'global_operations' => array(
            'all' => array(
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            )
        ),
        'operations' => array(
            'edit' => array(
                'label'      => &$GLOBALS['TL_LANG'][$strTable]['edit'],
                'href'       => 'act=edit',
                'icon'       => 'edit.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'copy' => array(
                'label'      => &$GLOBALS['TL_LANG'][$strTable]['copy'],
                'href'       => 'act=copy',
                'icon'       => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array(
                'label'      => &$GLOBALS['TL_LANG'][$strTable]['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\'Do you really want to delete this item?\')) return false; Backend.getScrollOffset()"'
            ),
            'show' => array(
                'label'      => &$GLOBALS['TL_LANG'][$strTable]['show'],
                'href'       => 'act=show',
                'icon'       => 'show.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'toggle' => array(
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array($strTable, 'toggleIcon')
            )
        )
    ],
    'palettes' => [
        'default' => '{season_legend},seasonName,startDate,endDate,minStay;{status_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'seasonName' => [
            'label' => ['Saisonname', 'Name der Saison (z.B. Winter, Hauptsaison, Silvester)'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'w50'],
            'sql' => "varchar(100) NOT NULL default ''",
            'search' => true,
        ],
        'startDate' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['startDate'],
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'date',
                'datepicker' => true,
                'minval' => time(),
                'tl_class' => 'w50 wizard',
            ],
            'save_callback' => [
                [$strTable, 'validateStartDate'],
                [$strTable, 'checkOverlapOnStartDate'],
            ],
            'sql' => "int(10) unsigned NULL",
        ],
        'endDate' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['endDate'],
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'date',
                'datepicker' => true,
                'minval' => time(),
                'tl_class' => 'w50 wizard',
            ],
            'save_callback' => [
                [$strTable, 'validateEndDate'],
                [$strTable, 'checkOverlapOnEndDate'],
            ],
            'sql' => "int(10) unsigned NULL",
        ],
        'minStay' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['minStay'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'minval' => 1, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '1'",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG'][$strTable]['published'],
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
            'filter' => true,
        ],
    ],
];

class tl_quickbooker extends Backend
{
    /**
     * Validate that startDate is in the future
     */
    public function validateStartDate($value, DataContainer $dc)
    {
        if (empty($value)) {
            return $value;
        }
        
        $timestamp = is_numeric($value) ? (int)$value : strtotime($value);
        $today = strtotime('today');
        
        if ($timestamp < $today) {
            throw new \Exception('Das Startdatum muss in der Zukunft liegen.');
        }
        
        return $value;
    }
    
    /**
     * Validate that endDate is in the future and after startDate
     */
    public function validateEndDate($value, DataContainer $dc)
    {
        if (empty($value)) {
            return $value;
        }
        
        $endTimestamp = is_numeric($value) ? (int)$value : strtotime($value);
        $today = strtotime('today');
        
        if ($endTimestamp < $today) {
            throw new \Exception('Das Enddatum muss in der Zukunft liegen.');
        }
        
        $startDate = Input::post('startDate');
        if ($startDate) {
            $startTimestamp = is_numeric($startDate) ? (int)$startDate : strtotime($startDate);
        } else {
            $startTimestamp = $dc->activeRecord->startDate ?? null;
        }
        
        if ($startTimestamp && $endTimestamp <= $startTimestamp) {
            throw new \Exception('Das Enddatum muss nach dem Startdatum liegen.');
        }
        
        return $value;
    }
    
    /**
     * Check for overlapping date ranges when saving startDate
     */
    public function checkOverlapOnStartDate($value, DataContainer $dc)
    {
        if (empty($value)) {
            return $value;
        }
        
        $startTimestamp = is_numeric($value) ? (int)$value : strtotime($value);
        
        // Get endDate from POST or existing record
        $endDate = Input::post('endDate');
        if ($endDate) {
            $endTimestamp = is_numeric($endDate) ? (int)$endDate : strtotime($endDate);
        } else {
            $endTimestamp = $dc->activeRecord->endDate ?? null;
        }
        
        if ($endTimestamp) {
            $this->checkDateRangeOverlap($startTimestamp, $endTimestamp, $dc->id);
        }
        
        return $value;
    }
    
    /**
     * Check for overlapping date ranges when saving endDate
     */
    public function checkOverlapOnEndDate($value, DataContainer $dc)
    {
        if (empty($value)) {
            return $value;
        }
        
        $endTimestamp = is_numeric($value) ? (int)$value : strtotime($value);
        
        // Get startDate from POST or existing record
        $startDate = Input::post('startDate');
        if ($startDate) {
            $startTimestamp = is_numeric($startDate) ? (int)$startDate : strtotime($startDate);
        } else {
            $startTimestamp = $dc->activeRecord->startDate ?? null;
        }
        
        if ($startTimestamp) {
            $this->checkDateRangeOverlap($startTimestamp, $endTimestamp, $dc->id);
        }
        
        return $value;
    }
    
    /**
     * Check if the given date range overlaps with any existing records
     * 
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @param int|null $excludeId Current record ID to exclude from check
     * @throws \Exception
     */
    protected function checkDateRangeOverlap($startTimestamp, $endTimestamp, $excludeId = null)
    {
        $db = Database::getInstance();
        
        // Two ranges overlap if: startA <= endB AND endA >= startB
        $sql = "SELECT id, seasonName, startDate, endDate 
                FROM tl_quickbooker 
                WHERE startDate <= ? 
                AND endDate >= ?";
        
        $params = [$endTimestamp, $startTimestamp];
        
        // Exclude current record when editing
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $db->prepare($sql)->execute(...$params);
        
        if ($result->numRows > 0) {
            $overlapping = $result->fetchAssoc();
            $existingStart = date('d.m.Y', (int)$overlapping['startDate']);
            $existingEnd = date('d.m.Y', (int)$overlapping['endDate']);
            
            throw new \Exception(sprintf(
                'Der Zeitraum überschneidet sich mit der Saison "%s" (%s - %s). Bitte wählen Sie einen anderen Zeitraum.',
                $overlapping['seasonName'],
                $existingStart,
                $existingEnd
            ));
        }
    }
    
    /**
     * Additional validation on submit (backup check)
     */
    public function validateNoOverlap(DataContainer $dc)
    {
        // This is called after individual field saves, acts as a final check
        // The field-level callbacks should have already caught any issues
    }
    
    /**
     * List a season record
     */
    public function listSeasons(array $row): string
    {
        $startDate = !empty($row['startDate']) ? date('d.m.Y', (int)$row['startDate']) : '-';
        $endDate = !empty($row['endDate']) ? date('d.m.Y', (int)$row['endDate']) : '-';
        
        return sprintf(
            '%s (%s - %s) -> mind. %d Nächte',
            htmlspecialchars($row['seasonName'] ?? 'Unbenannt'),
            $startDate,
            $endDate,
            (int)($row['minStay'] ?? 1)
        );
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible)
    {
        $objVersions = new Versions('tl_quickbooker', $intId);
        $objVersions->initialize();

        $this->Database->prepare("UPDATE tl_quickbooker SET tstamp=".time().", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
                     ->execute($intId);

        $objVersions->create();
    }
}