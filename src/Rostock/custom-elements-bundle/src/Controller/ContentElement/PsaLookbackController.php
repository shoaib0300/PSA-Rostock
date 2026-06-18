<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaLookback;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'psa_lookback', category: 'PSA Rostock', template: 'ce_psa_lookback', cache: false)]
final class PsaLookbackController extends AbstractContentElementController
{
    public function __construct(private readonly PsaLookback $lookback)
    {
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if (!System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest($request)) {
            $headline = StringUtil::deserialize($model->headline, true);
            $backend = new BackendTemplate('be_wildcard');
            $backend->title = $headline['value'] ?? 'PSA Lookback';
            $backend->wildcard = '### PSA Lookback ###';

            return $backend->getResponse();
        }

        $this->initializeContaoFramework();

        $GLOBALS['TL_CSS']['psa_lookback'] = 'bundles/customelements/frontend/css/psa_lookback.css';
        $GLOBALS['TL_BODY']['psa_lookback'] = FrontendTemplate::generateScriptTag(
            'bundles/customelements/frontend/js/psa_lookback.js',
            false,
            null,
        );

        $headline = StringUtil::deserialize($model->headline, true);
        $readerPage = $model->lookback_jumpTo ? PageModel::findById((int) $model->lookback_jumpTo) : null;
        $eventBaseUrl = $readerPage ? rtrim($this->generateContentUrl($readerPage), '/') : '';
        $year = trim((string) ($model->lookback_year ?? ''));

        $data = $this->lookback->build(
            (int) ($model->lookback_calendar ?? 0),
            (string) ($model->lookback_scope ?: 'past'),
            $year !== '' ? (int) $year : null,
            $eventBaseUrl,
        );

        $months = $data['months'];
        $splitAt = (int) ceil(\count($months) / 2);

        $template->set('headline', $headline['value'] ?? 'The Lookback');
        $template->set('hl', $headline['unit'] ?? 'h2');
        $template->set('lede', trim((string) ($model->subline ?? '')));
        $template->set('year', $data['year']);
        $template->set('monthsLeft', \array_slice($months, 0, $splitAt));
        $template->set('monthsRight', \array_slice($months, $splitAt));
        $template->set('slides', $data['slides']);
        $template->set('lang', $GLOBALS['TL_LANG']['PSA'] ?? []);

        return $template->getResponse();
    }
}
