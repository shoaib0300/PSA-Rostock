<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Rostock\CustomElementsBundle\Classes\PsaVote;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -32)]
final class PsaVoteActionListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly PsaVote $vote,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly ContaoFramework $framework,
        private readonly TokenStorageInterface $tokenStorage,
        #[Autowire('%contao.csrf_token_name%')]
        private readonly string $csrfTokenName,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->scopeMatcher->isFrontendRequest($request) || !$request->isMethod('POST')) {
            return;
        }

        if ((string) $request->request->get('FORM_SUBMIT', '') !== 'psa_vote_submit') {
            return;
        }

        $token = (string) $request->request->get('REQUEST_TOKEN', '');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($this->csrfTokenName, $token))) {
            if ($this->wantsJson($request)) {
                $event->setResponse(new JsonResponse(['ok' => false, 'error' => 'Invalid token.'], Response::HTTP_FORBIDDEN));
            }

            return;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_MEMBER')) {
            if ($this->wantsJson($request)) {
                $event->setResponse(new JsonResponse(['ok' => false, 'error' => 'Login required.'], Response::HTTP_UNAUTHORIZED));
            }

            return;
        }

        $user = $this->getFrontendUser();

        if ($user === null) {
            return;
        }

        $this->framework->initialize();
        $memberId = (int) $user->id;
        $campaignId = (int) $request->request->get('campaign_id', 0);
        $selections = $request->request->all('votes');

        if ($selections === []) {
            $selections = $request->request->get('votes', []);
        }

        $parsed = [];

        if (\is_array($selections)) {
            foreach ($selections as $reasonKey => $candidateId) {
                $parsed[(int) $reasonKey] = (int) $candidateId;
            }
        }

        $wantsJson = $this->wantsJson($request);

        try {
            $this->vote->submitBallot($campaignId, $memberId, $parsed);
            $campaign = $this->vote->getCampaign($campaignId, $memberId);
        } catch (\InvalidArgumentException $exception) {
            if ($wantsJson) {
                $event->setResponse(new JsonResponse(['ok' => false, 'error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST));
            }

            return;
        }

        if ($wantsJson) {
            $event->setResponse(new JsonResponse([
                'ok' => true,
                'campaign' => $campaign,
            ]));

            return;
        }

        $redirect = (string) ($request->request->get('redirect', '') ?: $request->getRequestUri());
        $event->setResponse(new RedirectResponse($redirect, Response::HTTP_SEE_OTHER));
    }

    private function wantsJson(Request $request): bool
    {
        return str_contains((string) $request->headers->get('Accept', ''), 'application/json')
            || $request->request->getBoolean('ajax');
    }

    private function getFrontendUser(): ?FrontendUser
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof FrontendUser ? $user : null;
    }
}
