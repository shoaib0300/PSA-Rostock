<?php

/**
 *Table tl_copyright
 */

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Contao\Versions;
use Contao\Input;
use Contao\StringUtil;
use Contao\Image;
use Contao\DC_Table;

$strTable = 'tl_copyright';

$GLOBALS['TL_DCA'][$strTable] = array(

    //Config
    'config' => array(
        'dataContainer' => DC_Table::class,
        'enableVersioning' => 'true',
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        ),
    ),
    //List
    'list' => array(
        'sorting' => array(
            'mode' => 1,
            'fields' => array('title'),
            'flag' => 1,
            'headerFields' => array('title'),
            'panelLayout' => 'filter;search,limit'
        ),
        'label' => array(
            'fields' => array('title'),
            'format' => '%s',
        ),
        'global_operations' => array(
            'all' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array(
            'edit' => array(
                'label' => &$GLOBALS['TL_LANG'][$strTable]['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG'][$strTable]['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'copy' => array(
                'label' => &$GLOBALS['TL_LANG'][$strTable]['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ),
            'toggle' => array(
                'label' => &$GLOBALS['TL_LANG'][$strTable]['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array($strTable, 'toggleIcon')
            ),
            'show' => array(
                'label' => &$GLOBALS['TL_LANG'][$strTable]['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right: 3px;"'
            ),
        )
    ),
    //Palettes
    'palettes' => array(
        'default' => 'title,published;'
    ),
    //Fields
    'fields' => array(
        'id' => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array(
            'label' => &$GLOBALS['TL_LANG'][$strTable]['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array(
                'mandatory' => true,
                'maxlength' => 100,
                'tl_class' => 'w50'
            ),
            'sql' => "varchar(100) NOT NULL default ''"
        ),
        'published' => array(
            'label' => &$GLOBALS['TL_LANG'][$strTable]['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => array(
                'mandatory' => false,
                'tl_class' => 'clr'
            ),
            'sql' => "varchar(1) NOT NULL default ''"
        )
    )

);

/**
 * Class tl_copyright
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package Controller
 */
class tl_copyright extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_copyright::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        if (!ContaoCorePermissions::USER_CAN_EDIT_ARTICLES) {
            return Image::getHtml($icon) . ' ';
        }

        return '<a href="' . $this->addToUrl(strRequest: $href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml(
            $icon,
            $label,
            'data-state="' . ($row['published'] ? 1 : 0) . '"'
        ) . '</a> ';
    }

    /**
     * Disable/enable a user group
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_copyright::published', 'alexf')) {
            $this->log('Not enough permissions to publish/unpublish article ID "' . $intId . '"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_copyright', $intId);
        $objVersions->initialize();

        // Update the database
        $this->Database->prepare("UPDATE tl_copyright SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }
}
