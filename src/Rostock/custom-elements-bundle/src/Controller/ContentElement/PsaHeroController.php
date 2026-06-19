<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\CeHelpers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'psa_hero', category: 'PSA Rostock', template: 'psa_hero')]
class PsaHeroController extends AbstractContentElementController
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm'];

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request)) {
            $GLOBALS['PSA_HAS_HERO'] = true;
            CeHelpers::registerButtonAssets();
            $GLOBALS['TL_CSS']['psa_hero'] = 'bundles/customelements/frontend/css/psa_hero.css';
            $GLOBALS['TL_BODY']['psa_hero'] = FrontendTemplate::generateScriptTag(
                'bundles/customelements/frontend/js/psa_hero.js',
                false,
                null
            );

            $headline = StringUtil::deserialize($model->headline, true);

            $template->set('headline', CeHelpers::plainText($headline['value'] ?? ''));
            $template->set('hl', $headline['unit'] ?? 'h1');
            $template->set('lede', CeHelpers::plainText((string) ($model->subline ?? '')));
            $template->set('addButton', (bool) $model->addButton);
            $template->set('button_label', CeHelpers::plainText((string) ($model->button_label ?? '')));
            $template->set('button_link', (string) ($model->button_link ?? ''));
            $template->set('button_target', (bool) $model->button_target);
            $template->set('slides', $this->buildSlides($model));
        } else {
            $template = new BackendTemplate('be_wildcard');
            $headline = StringUtil::deserialize($model->headline, true);
            $template->title = $headline['value'] ?? 'PSA Hero';
            $template->wildcard = '### PSA Hero ###';
        }

        return $template->getResponse();
    }

    /**
     * @return list<array{type: string, path: string, extension: string, alt: string}>
     */
    private function buildSlides(ContentModel $model): array
    {
        $slides = [];
        $uuids = StringUtil::deserialize($model->multiSRC, true) ?: [];

        foreach ($uuids as $uuid) {
            $file = FilesModel::findByUuid($uuid);

            if ($file === null) {
                continue;
            }

            $extension = strtolower(pathinfo($file->path, PATHINFO_EXTENSION));
            $slides[] = [
                'type' => \in_array($extension, self::VIDEO_EXTENSIONS, true) ? 'video' : 'image',
                'path' => $file->path,
                'extension' => $extension,
                'alt' => $file->name,
            ];
        }

        return $slides;
    }
}
