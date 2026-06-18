<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Rostock\CustomElementsBundle\Classes\PsaHeaderAuth;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -32)]
final class PsaMemberPageAccessListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            return;
        }

        $path = rtrim($request->getPathInfo(), '/') ?: '/';
        $isMember = $this->authorizationChecker->isGranted('ROLE_MEMBER');

        if ($path === '/account' && !$isMember) {
            $event->setResponse(new RedirectResponse(PsaHeaderAuth::getPageUrl('login')));

            return;
        }

        if ($path === '/register' && $isMember) {
            $event->setResponse(new RedirectResponse(PsaHeaderAuth::getPageUrl('login')));
        }
    }
}
