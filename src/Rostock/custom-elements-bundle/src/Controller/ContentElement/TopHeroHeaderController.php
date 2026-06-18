<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\FilesModel;
use Contao\FrontendTemplate;

#[AsContentElement(type: 'top_hero_header', category: 'custom_elements', template: 'top_hero_header')]
class TopHeroHeaderController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            $template->headline = $model->headline;
            $template->cssID = StringUtil::deserialize($model->cssID);
                        
        } else {
            // BACKEND
            $template             = new BackendTemplate("be_wildcard");
            $template->title = StringUtil::deserialize($model->headline)['value'];
            $template->wildcard = $model->text;
        }
        return $template->getResponse();
    }
}
