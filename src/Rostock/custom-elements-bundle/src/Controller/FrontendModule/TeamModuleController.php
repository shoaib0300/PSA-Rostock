<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\CeHelpers;
use Rostock\CustomElementsBundle\Classes\PsaTeam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(type: 'psa_team', category: 'PSA Rostock', template: 'mod_psa_team', cache: true)]
final class TeamModuleController extends AbstractFrontendModuleController
{
    public function __construct(private readonly PsaTeam $team)
    {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->initializeContaoFramework();

        $headline = \Contao\StringUtil::deserialize($model->headline, true);

        $template->set('headline', CeHelpers::plainText($headline['value'] ?? ''));
        $template->set('hl', $headline['unit'] ?? 'h1');
        $template->set('members', $this->team->getPublishedMembers());
        $template->set('lang', $GLOBALS['TL_LANG']['PSA'] ?? []);

        return $template->getResponse();
    }
}
