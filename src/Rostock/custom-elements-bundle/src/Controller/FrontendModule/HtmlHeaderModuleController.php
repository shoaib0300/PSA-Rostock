<?php

namespace Rostock\CustomElementsBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(type: 'html_header', category: 'PSA Rostock', template: 'mod_html_header')]
class HtmlHeaderModuleController extends AbstractFrontendModuleController
{
    private const DEFAULT_NAV = [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Events', 'href' => '/events'],
        ['label' => 'Meetups', 'href' => '/meetups'],
        ['label' => 'Team', 'href' => '/team'],
        ['label' => 'Contributors', 'href' => '/contributors'],
        ['label' => 'About', 'href' => '/about'],
    ];

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $headline = StringUtil::deserialize($model->headline, true);
        $currentPath = rtrim($request->getPathInfo(), '/') ?: '/';

        $template->set('siteName', $headline['value'] ?? 'PSA Rostock');
        $template->set('tagline', $model->text);
        $template->set('logoPath', $this->getFilePath($model->singleSRC));
        $template->set('registerUrl', $this->getRegisterUrl($model));
        $template->set('navigation', '');
        $template->set('navItems', array_map(
            static function (array $item) use ($currentPath): array {
                $hrefPath = rtrim($item['href'], '/') ?: '/';

                return [
                    'label' => $item['label'],
                    'href' => $item['href'],
                    'active' => $hrefPath === $currentPath,
                ];
            },
            self::DEFAULT_NAV,
        ));

        return $template->getResponse();
    }

    private function getFilePath(?string $uuid): string
    {
        if (!$uuid) {
            return '';
        }

        $file = FilesModel::findByUuid($uuid);

        return $file?->path ?? '';
    }

    private function getRegisterUrl(ModuleModel $model): string
    {
        if (!$model->jumpTo) {
            return '/register';
        }

        $page = PageModel::findById($model->jumpTo);

        if ($page === null) {
            return '/register';
        }

        return Controller::generateFrontendUrl($page->row());
    }
}
