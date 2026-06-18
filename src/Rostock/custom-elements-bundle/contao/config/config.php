<?php

use Contao\System;
use Rostock\CustomElementsBundle\Elements\ContentFilesCopyright;
use Rostock\CustomElementsBundle\Models\CopyrightModel;
use Rostock\CustomElementsBundle\Models\PsaMeetupCommentModel;
use Rostock\CustomElementsBundle\Models\PsaMeetupJoinModel;
use Rostock\CustomElementsBundle\Models\PsaMeetupPollOptionModel;
use Rostock\CustomElementsBundle\Models\PsaMeetupPollVoteModel;
use Rostock\CustomElementsBundle\Models\PsaMeetupModel;
use Rostock\CustomElementsBundle\Models\PsaTeamMemberModel;
use Rostock\CustomElementsBundle\Module\PsaModuleLostPassword;
use Rostock\CustomElementsBundle\Module\PsaModuleRegistration;

// Back end modules
$GLOBALS['BE_MOD']['booking_management']['quickbooker_module'] = [
    'tables' => ['tl_quickbooker'],
];

$GLOBALS['BE_MOD']['psa_community']['psa_meetups'] = [
    'tables' => ['tl_psa_meetup'],
];

$GLOBALS['BE_MOD']['psa_community']['psa_team'] = [
    'tables' => ['tl_psa_team_member'],
];

$GLOBALS['BE_MOD']['system']['copyright'] = array(
    'tables' => array('tl_copyright')
);

/**
 * Models
*/
$GLOBALS['TL_MODELS']['tl_quickbooker'] = QuickBookerModel::class;
$GLOBALS['TL_MODELS']['tl_copyright'] = CopyrightModel::class;
$GLOBALS['TL_MODELS']['tl_psa_event_rsvp'] = \Rostock\CustomElementsBundle\Models\PsaEventRsvpModel::class;
$GLOBALS['TL_MODELS']['tl_psa_meetup'] = PsaMeetupModel::class;
$GLOBALS['TL_MODELS']['tl_psa_meetup_join'] = PsaMeetupJoinModel::class;
$GLOBALS['TL_MODELS']['tl_psa_meetup_comment'] = PsaMeetupCommentModel::class;
$GLOBALS['TL_MODELS']['tl_psa_meetup_poll_option'] = PsaMeetupPollOptionModel::class;
$GLOBALS['TL_MODELS']['tl_psa_meetup_poll_vote'] = PsaMeetupPollVoteModel::class;
$GLOBALS['TL_MODELS']['tl_psa_team_member'] = PsaTeamMemberModel::class;

$GLOBALS['TL_CTE']['includes']['files_copyright'] = ContentFilesCopyright::class;

$GLOBALS['TL_BODY']['quick_booker_custom'] = \Contao\FrontendTemplate::generateScriptTag('bundles/customelements/frontend/js/easepick-custom.js');

$GLOBALS['FE_MOD']['application']['registration'] = PsaModuleRegistration::class;
$GLOBALS['FE_MOD']['application']['lostPassword'] = PsaModuleLostPassword::class;

System::loadLanguageFile('defaults', 'de');
System::loadLanguageFile('defaults', 'en');
System::loadLanguageFile('psa_member', 'de');
System::loadLanguageFile('psa_member', 'en');
System::loadLanguageFile('psa_events', 'de');
System::loadLanguageFile('psa_events', 'en');
System::loadLanguageFile('psa_meetups', 'de');
System::loadLanguageFile('psa_meetups', 'en');
System::loadLanguageFile('psa_team', 'de');
System::loadLanguageFile('psa_team', 'en');