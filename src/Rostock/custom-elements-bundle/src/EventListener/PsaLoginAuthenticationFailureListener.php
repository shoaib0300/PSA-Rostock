<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Rostock\CustomElementsBundle\Classes\PsaMemberAccess;
use Rostock\CustomElementsBundle\Classes\PsaMemberFlash;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

#[AsEventListener(event: LoginFailureEvent::class)]
final class PsaLoginAuthenticationFailureListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly PsaMemberFlash $flash,
    ) {
    }

    public function __invoke(LoginFailureEvent $event): void
    {
        if (!$this->scopeMatcher->isFrontendRequest($event->getRequest())) {
            return;
        }

        $username = (string) ($event->getRequest()->request->get('username') ?? $event->getPassport()?->getUser()?->getUserIdentifier() ?? '');

        if ($username === '') {
            return;
        }

        $member = PsaMemberAccess::findByLoginIdentifier($username);

        if ($member === null) {
            return;
        }

        if (!PsaMemberAccess::isLoginAllowed($member) || $event->getException() instanceof DisabledException) {
            $this->flash->set(PsaMemberFlash::TYPE_ACCOUNT_DEACTIVATED);
        }
    }
}
