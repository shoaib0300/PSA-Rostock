<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\ContentModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'ce_slider_main', category: 'Custom Elements', template: 'ce_slider_main')]
class CeSliderMainController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            // FRONTEND
            $GLOBALS['TL_BODY']['ce_slider_main']    = FrontendTemplate::generateScriptTag('bundles/customelements/frontend/js/ce_slider_main.js', false, null);
            $GLOBALS['TL_CSS']['ce_slider_main'] = "bundles/customelements/frontend/css/ce_slider_main.css";
            

            $template->multiSRC = StringUtil::deserialize($model->multiSRC);            
            $arrSubline = StringUtil::deserialize($model->subline);
            $template->subline = \is_array($arrSubline) ? $arrSubline['value'] ?? '' : $arrSubline;
            $template->sl = $arrSubline['unit'] ?? 'subline-h1';
        } else {
            // BACKEND
            $template             = new BackendTemplate("be_wildcard");
            $template->title = StringUtil::deserialize($model->headline)['value'];
            $template->wildcard = $model->text;
        }

        return $template->getResponse();
    }
}
