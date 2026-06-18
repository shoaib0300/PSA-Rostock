<?php

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'ce_start_hero', category: 'custom_elements', template: 'ce_start_hero')]
class CeStartHeroController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($request->attributes->get('_scope') === 'frontend') {
            // FRONTEND
            $GLOBALS['TL_CSS']['ce_start_hero'] = "bundles/customelements/frontend/css/ce_start_hero.css";
            $template->headline = 'dfg';
            $template->model = $model; // Make model available to the template

            return $template->getResponse();
        } else {
            // BACKEND
            $template = new BackendTemplate("be_wildcard");
            $template->title = StringUtil::deserialize($model->headline)['value'];
            $template->wildcard = $model->text;

            return $template->getResponse();
        }
    }
}
