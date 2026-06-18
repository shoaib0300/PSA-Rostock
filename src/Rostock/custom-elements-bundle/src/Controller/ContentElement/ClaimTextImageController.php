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

#[AsContentElement(type: 'claim_text_image', category: 'rapidskeleton', template: 'ce_claim_text_image')]
class ClaimTextImageController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            // FRONTEND
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
