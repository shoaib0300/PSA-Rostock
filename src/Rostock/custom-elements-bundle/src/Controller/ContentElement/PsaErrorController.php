<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\System;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\CeHelpers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'psa_error', category: 'PSA Rostock', template: 'ce_psa_error', cache: false)]
final class PsaErrorController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (!System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request)) {
            $backend = new BackendTemplate('be_wildcard');
            $backend->title = 'PSA Error page';
            $backend->wildcard = '### PSA Error ###';

            return $backend->getResponse();
        }

        $page = $request->attributes->get('pageModel');
        $code = $this->resolveErrorCode($page instanceof PageModel ? $page : null);
        $lang = $GLOBALS['TL_LANG']['PSA']['errors'][$code] ?? [];

        CeHelpers::registerButtonAssets();
        $GLOBALS['TL_CSS']['psa_error'] = 'bundles/customelements/frontend/css/psa_error.css';

        $template->set('code', $code);
        $template->set('title', $lang['title'] ?? 'Something went wrong');
        $template->set('message', $lang['message'] ?? '');
        $template->set('homeLabel', $GLOBALS['TL_LANG']['PSA']['error_home'] ?? 'Back to home');
        $template->set('homeUrl', $this->resolveHomeUrl());

        return $template->getResponse();
    }

    private function resolveErrorCode(?PageModel $page): int
    {
        if ($page === null) {
            return 0;
        }

        return match ($page->type) {
            'error_404' => 404,
            'error_403' => 403,
            'error_503' => 503,
            default => 0,
        };
    }

    private function resolveHomeUrl(): string
    {
        $rootId = 1;
        $root = PageModel::findById($rootId);
        $rootId = $root?->id ?? $rootId;

        foreach (PageModel::findBy('alias', 'index') ?? [] as $page) {
            if ((int) $page->pid === $rootId) {
                return $this->generateContentUrl($page);
            }
        }

        return '/';
    }
}
