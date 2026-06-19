<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\CeHelpers;
use Rostock\CustomElementsBundle\Classes\PsaHeaderAuth;
use Rostock\CustomElementsBundle\Classes\PsaVote;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsFrontendModule(type: 'psa_vote', category: 'PSA Rostock', template: 'mod_psa_vote', cache: false)]
final class VoteModuleController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly PsaVote $vote,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        #[Autowire('%contao.csrf_token_name%')]
        private readonly string $csrfTokenName,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->initializeContaoFramework();

        $auth = PsaHeaderAuth::resolve(rtrim($request->getPathInfo(), '/') ?: '/');
        $isMember = $this->authorizationChecker->isGranted('ROLE_MEMBER');
        $user = $this->tokenStorage->getToken()?->getUser();
        $memberId = $user instanceof FrontendUser ? (int) $user->id : 0;
        $headline = \Contao\StringUtil::deserialize($model->headline, true);

        $GLOBALS['TL_CSS']['psa_vote'] = 'bundles/customelements/frontend/css/psa_vote.css?v=4';
        $GLOBALS['TL_BODY']['psa_vote'] = \Contao\FrontendTemplate::generateScriptTag(
            'bundles/customelements/frontend/js/psa_vote.js?v=4',
        );

        $template->set('headline', CeHelpers::plainText($headline['value'] ?? ''));
        $template->set('hl', $headline['unit'] ?? 'h1');
        $template->set('isLoggedIn', $auth['isLoggedIn']);
        $template->set('isMember', $isMember);
        $template->set('memberId', $memberId);
        $template->set('loginUrl', $auth['loginUrl']);
        $template->set('campaigns', $this->vote->getVisibleCampaigns($memberId));
        $template->set('requestToken', $this->csrfTokenManager->getToken($this->csrfTokenName)?->getValue() ?? '');
        $template->set('lang', $GLOBALS['TL_LANG']['PSA'] ?? []);

        $response = $template->getResponse();
        $response->setPrivate();

        return $response;
    }
}
