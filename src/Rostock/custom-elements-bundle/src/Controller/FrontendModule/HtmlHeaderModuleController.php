<?php

namespace Rostock\CustomElementsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaHeaderAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(type: 'html_header', category: 'PSA Rostock', template: 'mod_html_header')]
class HtmlHeaderModuleController extends AbstractFrontendModuleController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->initializeContaoFramework();

        $headline = StringUtil::deserialize($model->headline, true);
        $auth = PsaHeaderAuth::resolve(rtrim($request->getPathInfo(), '/') ?: '/');

        $template->set('siteName', $headline['value'] ?? 'PSA Rostock');
        $template->set('tagline', $model->text);
        $template->set('logoPath', $this->getFilePath($model->singleSRC));
        $template->set('registerUrl', $this->getRegisterUrl($model));
        $template->set('navigation', '');
        $template->set('isLoggedIn', $auth['isLoggedIn']);
        $template->set('accountUrl', $auth['accountUrl']);
        $template->set('loginUrl', $auth['loginUrl']);
        $template->set('logoutUrl', $auth['logoutUrl']);
        $template->set('memberDisplayName', $auth['memberDisplayName']);
        $template->set('navItems', $auth['navItems']);

        $response = $template->getResponse();

        if ($auth['isLoggedIn']) {
            $response->setPrivate();
        }

        return $response;
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

        return $this->generateContentUrl($page);
    }
}
