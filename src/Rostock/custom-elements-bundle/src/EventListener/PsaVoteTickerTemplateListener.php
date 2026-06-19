<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaVote;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsHook('parseTemplate')]
class PsaVoteTickerTemplateListener
{
    public function __construct(
        private readonly PsaVote $vote,
        private readonly ContaoFramework $framework,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(Template $template): void
    {
        if ($template->getName() !== 'fe_page') {
            return;
        }

        $pageId = (int) ($GLOBALS['objPage']->id ?? 0);

        if (!$this->vote->shouldShowTickerOnPage($pageId)) {
            return;
        }

        $this->framework->initialize();

        $user = $this->tokenStorage->getToken()?->getUser();
        $memberId = $user instanceof FrontendUser ? (int) $user->id : 0;
        $items = $this->vote->buildTickerItems($memberId);

        if ($items === []) {
            return;
        }

        $lang = $GLOBALS['TL_LANG']['PSA'] ?? [];

        $template->psaVoteTicker = [
            'items' => $items,
            'votePageUrl' => $this->vote->resolveVotePageUrl(),
            'label' => $lang['vote_ticker_label'] ?? 'Live results',
            'winnerLabel' => $lang['vote_winner'] ?? 'Winner',
            'leadingLabel' => $lang['vote_leading'] ?? 'Leading',
        ];

        $GLOBALS['TL_CSS']['psa_vote_ticker'] = 'bundles/customelements/frontend/css/psa_vote_ticker.css?v=1';
        $GLOBALS['TL_BODY']['psa_vote_ticker'] = FrontendTemplate::generateScriptTag(
            'bundles/customelements/frontend/js/psa_vote_ticker.js?v=1',
        );
    }
}
