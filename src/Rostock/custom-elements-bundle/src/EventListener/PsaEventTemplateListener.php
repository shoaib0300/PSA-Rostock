<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\EventListener;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Input;
use Contao\System;
use Contao\Template;
use Rostock\CustomElementsBundle\Classes\PsaEventContentSanitizer;
use Rostock\CustomElementsBundle\Classes\PsaEventRsvp;
use Rostock\CustomElementsBundle\Classes\PsaHeaderAuth;

#[AsHook('parseTemplate')]
class PsaEventTemplateListener
{
    public function __construct(private readonly PsaEventRsvp $rsvp)
    {
    }

    public function __invoke(Template $template): void
    {
        $name = $template->getName();

        if (\in_array($name, ['mod_eventreader', 'mod_eventreader_psa'], true)) {
            $this->enrichEventReader($template);

            return;
        }

        if (\in_array($name, ['event_list', 'event_list_psa', 'event_teaser', 'event_teaser_psa'], true)) {
            $this->enrichEventListItem($template);
            $this->sanitizeEventFields($template);
            $this->addExcerpt($template);

            return;
        }

        if (\in_array($name, ['event_full', 'event_full_psa'], true)) {
            $this->sanitizeEventFields($template);
        }
    }

    private function enrichEventReader(Template $template): void
    {
        if (Input::get('auto_item') === null) {
            return;
        }

        $event = $this->resolveEvent();

        if ($event === null) {
            return;
        }

        $eventId = (int) $event->id;
        $tokenChecker = System::getContainer()->get('contao.security.token_checker');
        $isLoggedIn = $tokenChecker->hasFrontendUser();
        $memberVote = null;

        if ($isLoggedIn && ($user = FrontendUser::getInstance())?->id) {
            $memberVote = $this->rsvp->getVote($eventId, (int) $user->id);
        }

        $rsvpTemplate = new FrontendTemplate('psa_event_rsvp');
        $rsvpTemplate->eventId = $eventId;
        $rsvpTemplate->isLoggedIn = $isLoggedIn;
        $rsvpTemplate->loginUrl = PsaHeaderAuth::getPageUrl('login');
        $rsvpTemplate->registerUrl = PsaHeaderAuth::getPageUrl('register');
        $rsvpTemplate->counts = $this->rsvp->getCounts($eventId);
        $rsvpTemplate->voters = $this->rsvp->getVoterLists($eventId);
        $rsvpTemplate->memberVote = $memberVote;
        $rsvpTemplate->requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        $template->rsvp = $rsvpTemplate->parse();

        if ($template->allowComments && !$isLoggedIn) {
            $template->requireLogin = true;
            $template->login = $this->buildLoginMessage();
        }
    }

    private function enrichEventListItem(Template $template): void
    {
        $eventId = (int) ($template->id ?? 0);

        if ($eventId <= 0) {
            return;
        }

        $counts = $this->rsvp->getCounts($eventId);
        $template->rsvpYes = $counts['yes'];
        $template->rsvpNo = $counts['no'];
        $template->rsvpTotal = $counts['yes'] + $counts['no'];
    }

    private function sanitizeEventFields(Template $template): void
    {
        if (isset($template->teaser) && \is_string($template->teaser)) {
            $template->teaser = PsaEventContentSanitizer::sanitize($template->teaser);
        }

        if (isset($template->details) && \is_string($template->details)) {
            $template->details = PsaEventContentSanitizer::sanitize($template->details);
        }
    }

    private function addExcerpt(Template $template): void
    {
        $source = '';

        if (!empty($template->teaser) && \is_string($template->teaser)) {
            $source = $template->teaser;
        } elseif (!empty($template->details) && \is_string($template->details)) {
            $source = $template->details;
        }

        if ($source === '') {
            $template->excerpt = '';

            return;
        }

        $text = trim(html_entity_decode(strip_tags($source), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if (mb_strlen($text) > 140) {
            $text = mb_substr($text, 0, 137).'…';
        }

        $template->excerpt = $text;
    }

    private function resolveEvent(): ?CalendarEventsModel
    {
        $autoItem = Input::get('auto_item');

        if ($autoItem === null || $autoItem === '') {
            return null;
        }

        $calendarIds = [];

        foreach (CalendarModel::findAll() ?? [] as $calendar) {
            $calendarIds[] = (int) $calendar->id;
        }

        if ($calendarIds === []) {
            return null;
        }

        return CalendarEventsModel::findPublishedByParentAndIdOrAlias($autoItem, $calendarIds);
    }

    private function buildLoginMessage(): string
    {
        $loginUrl = htmlspecialchars(PsaHeaderAuth::getPageUrl('login'), ENT_QUOTES);

        return \sprintf(
            $GLOBALS['TL_LANG']['PSA']['event_login_required'] ?? 'Only members can vote and comment. Please <a href="%s">log in</a> first if you want to vote or comment.',
            $loginUrl,
        );
    }
}
