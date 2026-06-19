<?php

$GLOBALS['TL_LANG']['PSA']['vote_empty'] = 'No active voting campaign right now.';
$GLOBALS['TL_LANG']['PSA']['vote_login_required'] = 'Please log in as a member to vote.';
$GLOBALS['TL_LANG']['PSA']['vote_login_cta'] = 'Log in to vote';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_ends'] = 'Voting ends on %s';
$GLOBALS['TL_LANG']['PSA']['vote_countdown_left'] = '%s left';
$GLOBALS['TL_LANG']['PSA']['vote_countdown_starts_in'] = 'Opens in %s';
$GLOBALS['TL_LANG']['PSA']['vote_countdown_ended'] = 'Voting closed';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_not_open'] = 'Voting is not open yet.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_starts'] = 'Opens on %s.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_ended_on'] = 'Voting ended on %s.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_ended'] = 'This campaign has ended.';
$GLOBALS['TL_LANG']['PSA']['vote_winner'] = 'Winner';
$GLOBALS['TL_LANG']['PSA']['vote_winner_for'] = 'Winner for %s: %s';
$GLOBALS['TL_LANG']['PSA']['vote_leading'] = 'Leading';
$GLOBALS['TL_LANG']['PSA']['vote_leading_for'] = 'Leading for %s: %s';
$GLOBALS['TL_LANG']['PSA']['vote_ticker_label'] = 'Live results';
$GLOBALS['TL_LANG']['PSA']['vote_results_hidden'] = 'Results will be announced by the organisers.';
$GLOBALS['TL_LANG']['PSA']['vote_results_visible'] = 'Results are visible.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_upcoming'] = 'This campaign has not started yet.';
$GLOBALS['TL_LANG']['PSA']['vote_position'] = 'Position';
$GLOBALS['TL_LANG']['PSA']['vote_select_candidate'] = 'Choose a candidate';
$GLOBALS['TL_LANG']['PSA']['vote_submit'] = 'Submit vote';
$GLOBALS['TL_LANG']['PSA']['vote_change'] = 'Change vote';
$GLOBALS['TL_LANG']['PSA']['vote_your_choice'] = 'Your vote';
$GLOBALS['TL_LANG']['PSA']['vote_results'] = 'Results';
$GLOBALS['TL_LANG']['PSA']['vote_votes'] = '%d votes';
$GLOBALS['TL_LANG']['PSA']['vote_total_ballots'] = '%d members voted';
$GLOBALS['TL_LANG']['PSA']['vote_success'] = 'Your vote has been recorded.';
$GLOBALS['TL_LANG']['PSA']['vote_already_voted'] = 'You have already voted in this campaign.';
$GLOBALS['TL_LANG']['PSA']['vote_sidebar_title'] = 'Campaigns';
$GLOBALS['TL_LANG']['PSA']['vote_sidebar_voters'] = '%d voters';
$GLOBALS['TL_LANG']['PSA']['vote_sidebar_ballots'] = '%d votes cast';
$GLOBALS['TL_LANG']['PSA']['vote_sidebar_candidates'] = '%d candidates';
$GLOBALS['TL_LANG']['PSA']['vote_sidebar_you_voted'] = 'You voted';
$GLOBALS['TL_LANG']['PSA']['vote_status_active'] = 'Active';
$GLOBALS['TL_LANG']['PSA']['vote_status_upcoming'] = 'Upcoming';
$GLOBALS['TL_LANG']['PSA']['vote_status_ended'] = 'Ended';
$GLOBALS['TL_LANG']['PSA']['vote_incomplete'] = 'Please select a candidate for: %s';
$GLOBALS['TL_LANG']['PSA']['vote_hint_single'] = 'Click a person to select them, then click the submit button to submit your vote.';
$GLOBALS['TL_LANG']['PSA']['vote_hint_multi'] = 'Select one person for each position, then click the submit button to submit your vote.';
$GLOBALS['TL_LANG']['PSA']['vote_selected'] = 'Selected';

$GLOBALS['TL_LANG']['FMD']['psa_vote'] = ['PSA Voting', 'Shows active voting campaigns for members.'];

$GLOBALS['TL_LANG']['MOD']['psa_vote_reasons'] = ['Vote positions', 'Reusable positions/reasons for campaigns'];
$GLOBALS['TL_LANG']['MOD']['psa_vote_campaigns'] = ['Vote campaigns', 'Manage voting campaigns and candidates'];
$GLOBALS['TL_LANG']['MOD']['psa_vote_ballots'] = ['Vote ballots', 'Submitted member votes'];

$GLOBALS['TL_LANG']['tl_psa_vote_reason'] = [
    'reason_legend' => 'Position',
    'publish_legend' => 'Publishing',
    'title' => ['Title', 'Position or role title (e.g. Chairperson)'],
    'photo' => ['Image', 'Optional icon or image for this position'],
    'description' => ['Description', 'Short explanation of this position'],
    'published' => ['Publish', 'Allow selecting this position in campaigns'],
];

$GLOBALS['TL_LANG']['tl_psa_vote_campaign'] = [
    'campaign_legend' => 'Campaign',
    'publish_legend' => 'Publishing',
    'title' => ['Title', 'Campaign name shown on the frontend'],
    'description' => ['Description', 'Intro text for voters'],
    'startDate' => ['Start', 'Pick voting start date in the calendar (empty = immediately)'],
    'endDate' => ['End', 'Pick voting end date in the calendar (empty = no end)'],
    'showResults' => ['Show results', 'When members can see vote counts'],
    'published' => ['Publish campaign', 'Make this campaign visible on the frontend'],
    'ballotCount' => '%d votes',
    'statusRef' => [
        'draft' => 'Draft',
        'upcoming' => 'Upcoming',
        'active' => 'Active',
        'ended' => 'Ended',
    ],
    'showResultsRef' => [
        'after_vote' => 'After member voted',
        'after_end' => 'After campaign ends',
        'always' => 'Always',
        'never' => 'Never (admin only)',
    ],
];

$GLOBALS['TL_LANG']['tl_psa_vote_candidate'] = [
    'candidate_legend' => 'Candidate',
    'publish_legend' => 'Publishing',
    'reason_id' => ['Position template', 'Pick a saved position or leave empty and enter a custom position'],
    'name' => ['Person', 'Candidate name'],
    'photo' => ['Photo', 'Candidate portrait'],
    'position' => ['Custom position', 'Used when no position template is selected'],
    'description' => ['Pitch', 'Short statement from the candidate'],
    'published' => ['Publish candidate', 'Show this person in the campaign'],
    'no_position' => 'General',
];

$GLOBALS['TL_LANG']['ERR']['voteEndBeforeStart'] = 'The end date must be on or after the start date.';

$GLOBALS['TL_LANG']['tl_psa_vote_ballot'] = [
    'campaign_id' => ['Campaign', ''],
    'reason_id' => ['Position', ''],
    'candidate_id' => ['Candidate', ''],
    'member_id' => ['Member', ''],
];
