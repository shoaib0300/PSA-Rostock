<?php

namespace Rostock\CustomElementsBundle\Classes;

use Contao\ArticleModel;
use Contao\ContentModel;
use Rostock\CustomElementsBundle\Models\CopyrightModel;
use Contao\Frontend;
use Contao\LayoutModel;
use Contao\Image;
use Contao\ImageSizeModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\ThemeModel;
use Contao\FilesModel;

class CeHelpers
{
    public static function getRootPageByContentId($cid) {
        $objContent = ContentModel::findById($cid);
        $objArticle = ArticleModel::findById($objContent->pid);
        $pid = $objArticle->pid;
        while ($pid != 0) {
            $objPage = PageModel::findById($pid);
            $pid = $objPage->pid;
        }
        return $objPage;
    }

    public static function getFallbackRootpageByRootpage($rootpage) {
        $objPage = PageModel::findOneBy(
            ['dns = ?', 'fallback = ?'],
            [$rootpage->dns, '1']
        );
        return $objPage;
    }

    public static function getRootPageByPageId($pid) {
        while ($pid != 0) {
            $objRootPage = PageModel::findById($pid);
            $pid = $objRootPage->pid;
        }
        return $objRootPage;
    }

    public static function getThemeByRootPageId($rpid) {
        $objPage = PageModel::findById($rpid);
        $objLayout = LayoutModel::findById($objPage->layout);
        $objTheme = ThemeModel::findById($objLayout->pid);
        return $objTheme;
    }

    public static function generateHeadline($hl, $headline) {
        $hl_end_tag = $hl;
        if(substr($hl, 0, 1) == 'p') $hl_end_tag = substr($hl, 0, 1);
        if(substr($hl, 0, 1) == 'h') $hl_end_tag = substr($hl, 0, 2);
        return '<' . $hl . '>' . $headline . '</' . $hl_end_tag . '>';
    }

    public static function generateMultiLangItems($serializedMultiLangItems) {
        $arrTmpMultiLangItems = StringUtil::deserialize($serializedMultiLangItems);
        $arrItemKeys = array_keys($arrTmpMultiLangItems[0]);
        $langKey = array_shift($arrItemKeys);
        $arrMultiLangItems = [];
        foreach ($arrTmpMultiLangItems AS $ml_item) {
            if(is_array($arrItemKeys) && count($arrItemKeys)) {
                foreach ($arrItemKeys AS $key) {
                    $arrMultiLangItems[$ml_item[$langKey]][$key] = $ml_item[$key];
                }
            }
        }
        return $arrMultiLangItems;
    }

    public static function truncateText($string,$length=100,$append=" &hellip;")
    {
        $string = strip_tags(trim(preg_replace('/\s+/', ' ', $string)));

        if(strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return $string;
    }

    public static function addMetaToImage($objImage, $size = '') {
        global $objPage;

        $arrMeta = Frontend::getMetaData($objImage->meta, $objPage->language);
        $image = Image::generateImageSRC($objImage->uuid, $size);
        $alt = $arrMeta['alt'];
        if(!$alt) {
            $alt = $arrMeta['title'];
        }
        $title = $arrMeta['title'];
        if(!$title) {
            $title = $arrMeta['alt'];
        }
        if($arrMeta['caption']) {
            $title = $arrMeta['caption'];
        }
        $image['picture']->picture['alt'] = $alt;
        $image['picture']->picture['title'] = $title;

        return $image;
    }

    public static function generateUrlImageSrc($url, $imageSizeId, $lazy, $additionalTags = []): string {
        $imgSize = ImageSizeModel::findById($imageSizeId);
        $width = $imgSize->width . "px";
        $height = $imgSize->height . "px";
        $name = $imgSize->name;
        $loadingTag = $lazy ? " loading='lazy'" : "";

        $tagHtml = "";
        if (!empty($additionalTags)) {
            foreach ($additionalTags as $tag => $value) {
                $tagHtml .= " $tag='$value'";
            }
        }

        return "<img src='$url' alt='$name' style='width:$width;height:$height;object-fit:cover;' $tagHtml$loadingTag>";
    }

    public static function getFileMetaData($fileField)
    {
        if (!$fileField) {
            return null;
        }

        $fileData = StringUtil::deserialize($fileField, true);

        $uuid = is_array($fileData) ? ($fileData[0]['uuid'] ?? $fileData[0]) : $fileData;

        if (!$uuid) {
            return null;
        }

        $objFile = FilesModel::findByUuid($uuid);
        if (!$objFile) {
            return null;
        }

        $meta = StringUtil::deserialize($objFile->meta, true);
        $metaData = $meta['de'] ?? [];

        $copyright = null;
        if (!empty($objFile->copyright)) {
            $copyright = CopyrightModel::findByIdOrAlias($objFile->copyright);
        }


        return [
            'path' => $objFile->path,
            'name' => $objFile->name,
            'uuid' => $objFile->uuid,
            'extension' => $objFile->extension,
            'size' => $objFile->size,
            'meta' => $metaData,
            'copyright' => $copyright ?: null,
            'copyrightGroup' => $objFile->copyrightStockPhotographyGroup,
            'copyrightPosition' => $objFile->copyrightPosition,
        ];
    }

    public static function getCopyRightByImagePath($path) {
        $fileObj = FilesModel::findByPath($path);
        if (!empty($fileObj->copyright)) {
            return CopyrightModel::findByIdOrAlias($fileObj->copyright);
        }

        return null;
    }

    public static function getPathOfImage($image) {
        if (!empty($image)) {
            return $image["picture"]->singleSRC;
        }
        return null;
    }
}