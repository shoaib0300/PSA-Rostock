<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Rostock\CustomElementsBundle\Classes\PsaHeaderAuth;
use Rostock\CustomElementsBundle\Classes\PsaMeetup;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -32)]
final class PsaMeetupActionListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly PsaMeetup $meetup,
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

        $formSubmit = (string) $request->request->get('FORM_SUBMIT', '');

        if (!\in_array($formSubmit, [
            'psa_meetup_create',
            'psa_meetup_join',
            'psa_meetup_comment',
            'psa_delete_meetup_comment',
            'psa_delete_meetup',
            'psa_meetup_poll_vote',
            'psa_meetup_comment_reaction',
        ], true)) {
            return;
        }

        $token = (string) $request->request->get('REQUEST_TOKEN', '');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($this->csrfTokenName, $token))) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_MEMBER')) {
            return;
        }

        $user = $this->getFrontendUser();

        if ($user === null) {
            return;
        }

        $this->framework->initialize();
        $memberId = (int) $user->id;

        try {
            match ($formSubmit) {
                'psa_meetup_create' => $this->handleCreate($request, $memberId),
                'psa_meetup_join' => $this->handleJoin($request, $memberId),
                'psa_meetup_comment' => $this->handleComment($request, $memberId),
                'psa_delete_meetup_comment' => $this->handleDeleteComment($request, $memberId),
                'psa_delete_meetup' => $this->handleDeleteMeetup($request, $memberId),
                'psa_meetup_poll_vote' => $this->handlePollVote($request, $memberId),
                'psa_meetup_comment_reaction' => $this->handleCommentReaction($request, $memberId),
            };
        } catch (\InvalidArgumentException) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->resolveRedirectUrl($request), Response::HTTP_SEE_OTHER));
    }

    private function handleCreate(Request $request, int $memberId): void
    {
        $title = (string) $request->request->get('title', '');
        $description = (string) $request->request->get('description', '');
        $location = (string) $request->request->get('location', '');
        $postType = (string) $request->request->get('post_type', 'meetup');
        $pollQuestion = (string) $request->request->get('poll_question', '');
        $addPoll = (string) $request->request->get('add_poll', '') === '1';
        $pollOptions = $request->request->all('poll_options');
        $meetupDateRaw = trim((string) $request->request->get('meetup_date', ''));
        $meetupDate = 0;

        if ($meetupDateRaw !== '') {
            $parsed = strtotime($meetupDateRaw);

            if ($parsed !== false) {
                $meetupDate = $parsed;
            }
        }

        if (!$addPoll) {
            $pollQuestion = '';
            $pollOptions = [];
        }

        $this->meetup->createMeetup(
            $memberId,
            $title,
            $description,
            $meetupDate,
            $location,
            $postType,
            $pollQuestion,
            \is_array($pollOptions) ? $pollOptions : [],
        );
    }

    private function handlePollVote(Request $request, int $memberId): void
    {
        $meetupId = (int) $request->request->get('meetup_id', 0);
        $optionId = (int) $request->request->get('option_id', 0);

        if ($meetupId <= 0 || $optionId <= 0) {
            throw new \InvalidArgumentException('Invalid poll vote.');
        }

        $this->meetup->votePoll($meetupId, $optionId, $memberId);
    }

    private function handleJoin(Request $request, int $memberId): void
    {
        $meetupId = (int) $request->request->get('meetup_id', 0);
        $status = (string) $request->request->get('join_status', '');

        if ($meetupId <= 0 || !\in_array($status, ['join', 'decline'], true)) {
            throw new \InvalidArgumentException('Invalid meetup.');
        }

        $meetup = $this->meetup->getPublishedMeetup($meetupId, $memberId);

        if ($meetup === null || empty($meetup['isMeetup'])) {
            throw new \InvalidArgumentException('Invalid meetup.');
        }

        $this->meetup->setJoinStatus($meetupId, $memberId, $status);
    }

    private function handleCommentReaction(Request $request, int $memberId): void
    {
        $commentId = (int) $request->request->get('comment_id', 0);
        $emoji = (string) $request->request->get('emoji', '');

        if ($commentId <= 0 || $emoji === '') {
            throw new \InvalidArgumentException('Invalid reaction.');
        }

        $this->meetup->toggleCommentReaction($commentId, $memberId, $emoji);
    }

    private function handleComment(Request $request, int $memberId): void
    {
        $meetupId = (int) $request->request->get('meetup_id', 0);
        $comment = (string) $request->request->get('comment', '');

        if ($meetupId <= 0 || $this->meetup->getPublishedMeetup($meetupId, $memberId) === null) {
            throw new \InvalidArgumentException('Invalid meetup.');
        }

        $this->meetup->addComment($meetupId, $memberId, $comment);
    }

    private function handleDeleteComment(Request $request, int $memberId): void
    {
        $commentId = (int) $request->request->get('comment_id', 0);

        if ($commentId <= 0 || !$this->meetup->deleteComment($commentId, $memberId)) {
            throw new \InvalidArgumentException('Cannot delete comment.');
        }
    }

    private function handleDeleteMeetup(Request $request, int $memberId): void
    {
        $meetupId = (int) $request->request->get('meetup_id', 0);

        if ($meetupId <= 0 || !$this->meetup->deleteMeetup($meetupId, $memberId)) {
            throw new \InvalidArgumentException('Cannot delete meetup.');
        }
    }

    private function resolveRedirectUrl(Request $request): string
    {
        if ($request->request->get('return_to') === 'account') {
            return PsaHeaderAuth::getPageUrl('account').'#psa-member-account-posts';
        }

        $referer = $request->headers->get('Referer');

        if (\is_string($referer) && $referer !== '') {
            return $referer;
        }

        return $request->getSchemeAndHttpHost().'/meetups';
    }

    private function getFrontendUser(): ?FrontendUser
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof FrontendUser ? $user : null;
    }
}
