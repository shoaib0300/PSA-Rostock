<?php

namespace Rostock\CustomElementsBundle\Elements;

use Contao\ContentElement;
use Contao\Database;
use Rostock\CustomElementsBundle\Classes\FilesCopyright;
use Rostock\CustomElementsBundle\Models\CopyrightModel;

/**
 * Class ContentFilesCopyright
 *
 * Front end content element "Files Copyright".
 */
class ContentFilesCopyright extends ContentElement
{

    protected static $SQL = <<<SQL
        SELECT DISTINCT
            TRIM(`copyright`) AS `copyright`,
            `copyrightStockPhotographyGroup`
        FROM
            `tl_files`
        WHERE
            `type` = 'file'
                AND TRIM(`copyright`) <> ''
        ORDER BY `copyright` ASC , `copyrightStockPhotographyGroup` ASC
        SQL;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_files_copyright';

    /**
     * Generate the content element
     */
    protected function compile()
    {
        $copyrights = array();

        $stmt = Database::getInstance()->prepare(static::$SQL);
        if ($result = $stmt->execute()) {
            $groups = FilesCopyright::stockPhotographyGroupOptionsReference();
            while ($result->next()) {
                $objCopyright = CopyrightModel::findBy(['id=?', 'published=1'], [$result->copyright]);

                if($objCopyright !== NULL) {
                    $copyright = array(
                        'name' => $objCopyright->title,
                        'group' => $result->copyrightStockPhotographyGroup ? $groups[(int) $result->copyrightStockPhotographyGroup] : null
                    );
                    $copyrights[] = $copyright;
                }
            }
        }

        $this->Template->copyrights = $copyrights;
    }
}
