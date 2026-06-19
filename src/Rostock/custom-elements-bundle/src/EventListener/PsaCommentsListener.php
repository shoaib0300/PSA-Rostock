<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CommentsModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\System;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaMemberAvatar;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsHook('parseTemplate')]
class PsaCommentsListener
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(Template $template): void
    {
        $name = $template->getName();

        if ($name === 'com_default_psa') {
            $this->enrichComment($template);

            return;
        }

        if ($name === 'mod_comment_form_psa') {
            $this->simplifyCommentForm($template);
        }
    }

    private function enrichComment(Template $template): void
    {
        $commentId = $this->resolveCommentId($template);
        $template->commentId = $commentId;
        $template->authorName = $this->resolveAuthorName($template);
        $template->authorAvatarUrl = $this->resolveAuthorAvatarUrl($template);
        $template->requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $template->canDelete = $this->canDeleteComment($commentId, (int) ($template->member ?? 0));
    }

    private function simplifyCommentForm(Template $template): void
    {
        if (!empty($template->requireLogin) || empty($template->fields) || !\is_array($template->fields)) {
            return;
        }

        if (isset($template->fields['comment'])) {
            $template->fields['comment']->label = $GLOBALS['TL_LANG']['PSA']['comment_label'] ?? $template->fields['comment']->label;
            $template->fields['comment']->placeholder = $GLOBALS['TL_LANG']['PSA']['comment_placeholder'] ?? '';
        }
    }

    private function resolveCommentId(Template $template): int
    {
        if (isset($template->commentId)) {
            return (int) $template->commentId;
        }

        $id = $template->id ?? '';

        if (\is_string($id) && str_starts_with($id, 'c')) {
            return (int) substr($id, 1);
        }

        return (int) $id;
    }

    private function resolveAuthorName(Template $template): string
    {
        $memberId = (int) ($template->member ?? 0);

        if ($memberId > 0 && ($member = MemberModel::findById($memberId))) {
            $nickname = trim((string) ($member->nickname ?? ''));

            if ($nickname !== '') {
                return $nickname;
            }

            $fullName = trim((string) ($member->firstname ?? '').' '.(string) ($member->lastname ?? ''));

            if ($fullName !== '') {
                return $fullName;
            }

            return (string) ($member->username ?? $template->name ?? '');
        }

        return (string) ($template->name ?? '');
    }

    private function resolveAuthorAvatarUrl(Template $template): string
    {
        $memberId = (int) ($template->member ?? 0);

        if ($memberId <= 0) {
            return '';
        }

        $member = MemberModel::findById($memberId);

        if ($member === null) {
            return '';
        }

        return PsaMemberAvatar::resolvePath($member->avatar) ?? '';
    }

    private function canDeleteComment(int $commentId, int $memberId): bool
    {
        if ($commentId <= 0 || $memberId <= 0) {
            return false;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_MEMBER')) {
            return false;
        }

        $user = FrontendUser::getInstance();

        if ((int) ($user?->id ?? 0) !== $memberId) {
            return false;
        }

        $comment = CommentsModel::findByPk($commentId);

        return $comment !== null
            && (int) $comment->member === $memberId
            && $comment->source === 'tl_calendar_events';
    }
}
