<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CommentsModel;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -32)]
final class PsaCommentDeleteListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
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

        if ($request->request->get('FORM_SUBMIT') !== 'psa_delete_comment') {
            return;
        }

        $commentId = (int) $request->request->get('comment_id', 0);
        $token = (string) $request->request->get('REQUEST_TOKEN', '');

        if (
            $commentId <= 0
            || !$this->csrfTokenManager->isTokenValid(new CsrfToken($this->csrfTokenName, $token))
            || !$this->authorizationChecker->isGranted('ROLE_MEMBER')
        ) {
            return;
        }

        $user = $this->getFrontendUser();

        if ($user === null) {
            return;
        }

        $this->framework->initialize();

        $comment = CommentsModel::findByPk($commentId);

        if (
            $comment === null
            || (int) $comment->member !== (int) $user->id
            || $comment->source !== 'tl_calendar_events'
        ) {
            return;
        }

        $comment->delete();

        $referer = $request->headers->get('Referer');

        $event->setResponse(new RedirectResponse(
            \is_string($referer) && $referer !== '' ? $referer : '/events',
            Response::HTTP_SEE_OTHER,
        ));
    }

    private function getFrontendUser(): ?FrontendUser
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof FrontendUser ? $user : null;
    }
}
