<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\Input;
use Rostock\CustomElementsBundle\Classes\PsaEventRsvp;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final class PsaEventRsvpListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly PsaEventRsvp $rsvp,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
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

        if ($request->request->get('FORM_SUBMIT') !== 'psa_event_rsvp') {
            return;
        }

        $eventId = (int) $request->request->get('event_id', 0);
        $status = (string) $request->request->get('status', '');
        $token = (string) $request->request->get('REQUEST_TOKEN', '');

        if (
            $eventId <= 0
            || !\in_array($status, ['yes', 'no'], true)
            || !$this->csrfTokenManager->isTokenValid(new CsrfToken('contao_csrf_token', $token))
        ) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_MEMBER')) {
            return;
        }

        $calendarEvent = CalendarEventsModel::findByPk($eventId);

        if ($calendarEvent === null || !$calendarEvent->published) {
            return;
        }

        $user = FrontendUser::getInstance();

        if (!$user?->id) {
            return;
        }

        $this->rsvp->vote($eventId, (int) $user->id, $status);

        $redirect = Input::get('auto_item')
            ? $request->getRequestUri()
            : ($request->headers->get('Referer') ?: '/events');

        $event->setResponse(new RedirectResponse($redirect));
    }
}
