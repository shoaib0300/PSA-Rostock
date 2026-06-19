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

    /**
     * Decode text stored with HTML entities from the Contao backend.
     */
    public static function plainText(string|null $value): string
    {
        return StringUtil::decodeEntities(trim((string) $value));
    }

    /**
     * Decode Contao entity storage, then escape for safe HTML output.
     */
    public static function esc(string|null $value): string
    {
        return htmlspecialchars(self::plainText($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

    public static function registerButtonAssets(): void
    {
        $GLOBALS['TL_CSS']['psa_button'] = 'bundles/customelements/frontend/css/psa_button.css';
    }

    /**
     * Standard outer wrapper for custom content elements.
     *
     * @param array{compact?: bool, flush?: bool, extra?: string} $options
     */
    public static function psaScreenClass(string $componentClass = '', array $options = []): string
    {
        $classes = array_filter([
            'psa-screen',
            $componentClass !== '' ? $componentClass : null,
            !empty($options['compact']) ? 'psa-screen--compact' : null,
            !empty($options['flush']) ? 'psa-screen--flush' : null,
            $options['extra'] ?? null,
        ]);

        return implode(' ', $classes);
    }

    /**
     * Standard inner container (max-width + side padding).
     */
    public static function psaContainerClass(bool $fullWidth = false): string
    {
        return $fullWidth ? 'psa-container psa-container--full' : 'psa-container';
    }

    /**
     * Render the global PSA button markup (class psa-hero__btn).
     *
     * @param array{href?: string|null, target?: string, class?: string, type?: string, attrs?: array<string, string>} $options
     */
    public static function renderPsaButton(string $label, array $options = []): string
    {
        $href = $options['href'] ?? null;
        $target = (string) ($options['target'] ?? '');
        $class = trim('psa-hero__btn ' . ($options['class'] ?? ''));
        $buttonType = (string) ($options['type'] ?? 'button');
        $extraAttrs = $options['attrs'] ?? [];

        $attrs = ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"';

        if ($href !== null && $href !== '') {
            $tag = 'a';
            $attrs .= ' href="' . htmlspecialchars($href, ENT_QUOTES) . '"';

            if ($target !== '') {
                $attrs .= ' target="' . htmlspecialchars($target, ENT_QUOTES) . '"';

                if ($target === '_blank') {
                    $attrs .= ' rel="noopener noreferrer"';
                }
            }
        } else {
            $tag = 'button';
            $attrs .= ' type="' . htmlspecialchars($buttonType, ENT_QUOTES) . '"';
        }

        foreach ($extraAttrs as $key => $value) {
            $attrs .= ' ' . htmlspecialchars((string) $key, ENT_QUOTES) . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
        }

        $labelEsc = htmlspecialchars($label, ENT_QUOTES);

        return '<' . $tag . $attrs . '>'
            . '<span class="psa-hero__btn-label">'
            . '<span class="psa-hero__btn-label-text">' . $labelEsc . '</span>'
            . '<span class="psa-hero__btn-corner" aria-hidden="true">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="48" fill="none" viewBox="0 0 18 48">'
            . '<path class="psa-hero__btn-path--corner-default" fill="#222F30" d="M0 0h5.63c7.808 0 13.536 7.337 11.642 14.91l-6.09 24.359A11.527 11.527 0 0 1 0 48V0Z"/>'
            . '<path class="psa-hero__btn-path--corner-hover" fill="#CEF79E" d="M0 0c5.29 0 9.9 3.6 11.183 8.731l6.09 24.359C19.165 40.663 13.437 48 5.63 48H0V0Z"/>'
            . '</svg>'
            . '</span>'
            . '</span>'
            . '<span class="psa-hero__btn-icon" aria-hidden="true">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="51" height="48" fill="none" viewBox="0 0 51 48">'
            . '<path class="psa-hero__btn-path--icon-default" fill="currentColor" d="M6.728 9.09A12 12 0 0 1 18.369 0H39c6.627 0 12 5.373 12 12v24c0 6.627-5.373 12-12 12H12.37C4.561 48-1.167 40.663.727 33.09l6-24Z"/>'
            . '<path class="psa-hero__btn-path--icon-hover" fill="currentColor" d="M.728 14.91C-1.166 7.338 4.562 0 12.369 0H39c6.628 0 12 5.373 12 12v24c0 6.627-5.372 12-12 12H18.37a12 12 0 0 1-11.641-9.09l-6-24Z"/>'
            . '</svg>'
            . '</span>'
            . '</' . $tag . '>';
    }
}