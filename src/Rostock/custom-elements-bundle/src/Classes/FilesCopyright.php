<?php

namespace Rostock\CustomElementsBundle\Classes;

use Rostock\CustomElementsBundle\Models\CopyrightModel;
use Contao\Database;

class FilesCopyright
{
    static $STOCK_PHOTOGRAPHY_GROUPS = array(
        1 => 'Adobe Stock'
    );

    public static function stockPhotographyGroupOptions()
    {
        return array_keys(static::$STOCK_PHOTOGRAPHY_GROUPS);
    }

    public static function stockPhotographyGroupOptionsReference()
    {
        return static::$STOCK_PHOTOGRAPHY_GROUPS;
    }

    public static function copyrightOptions()
    {
        $objDatabase = Database::getInstance();
        $arrTables = $objDatabase->listTables();
        $arrOptions = [];
        if (\in_array('tl_copyright', $arrTables)) {
            $objCopyright = CopyrightModel::findAll();
            if ($objCopyright) {
                foreach ($objCopyright as $copyright) {
                    $arrOptions[$copyright->id] = $copyright->title;
                }
            }
        }
        return $arrOptions;
    }

    public static function copyrightLoadCallback($val, $dc)
    {
        $objCopyright = CopyrightModel::findBy(["tl_copyright.id=?"], [$val]);
        if (!$objCopyright) {
            $objCopyright = CopyrightModel::findBy(["tl_copyright.title=?"], [$val]);
            return $objCopyright->id ?? null;
        } else {
            return (int) $val;
        }
    }
}
