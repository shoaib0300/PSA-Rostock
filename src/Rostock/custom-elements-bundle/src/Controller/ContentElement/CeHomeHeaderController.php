<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\ContentModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\FilesModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'ce_home_header', category: 'Custom Elements', template: 'ce_home_header')]
class CeHomeHeaderController extends AbstractContentElementController
{

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        // Your existing code...
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            // FRONTEND

            $template->multiSRC = StringUtil::deserialize($model->multiSRC);
    
            $arrSubline = StringUtil::deserialize($model->subline);
            $template->subline = \is_array($arrSubline) ? ($arrSubline['value'] ?? '') : $arrSubline;
            $template->sl = $arrSubline['unit'] ?? 'subline-h1';
    
            $template->informationHeadline = $model->information_headline;
            $template->informationBoxText = $model->information_box_text;
            $template->backgroundVideo = $model->backgroundVideo;
            $template->calimImage = $model->claim_image_right;
        }
    
        // Return the final response with the template rendering
        return $template->getResponse();
    }
    
}


