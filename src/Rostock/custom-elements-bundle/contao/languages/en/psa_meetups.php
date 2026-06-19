<?php

$GLOBALS['TL_LANG']['PSA']['meetup_page_title'] = 'Meetups';
$GLOBALS['TL_LANG']['PSA']['meetup_page_intro'] = 'Plan an outing and invite other members to join you.';
$GLOBALS['TL_LANG']['PSA']['meetup_create_title'] = 'Create post';
$GLOBALS['TL_LANG']['PSA']['meetup_create_open'] = 'Create meetup';
$GLOBALS['TL_LANG']['PSA']['meetup_modal_close'] = 'Close';
$GLOBALS['TL_LANG']['PSA']['meetup_modal_cancel'] = 'Cancel';
$GLOBALS['TL_LANG']['PSA']['meetup_type_label'] = 'What do you want to share?';
$GLOBALS['TL_LANG']['PSA']['meetup_type_meetup'] = 'Meetup';
$GLOBALS['TL_LANG']['PSA']['meetup_type_post'] = 'Simple post';
$GLOBALS['TL_LANG']['PSA']['meetup_add_poll'] = 'Add a poll for members';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_question'] = 'Poll question';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_question_placeholder'] = 'Which day works best?';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_options'] = 'Options';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_option_placeholder'] = 'Option';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_add_option'] = '+ Add option';
$GLOBALS['TL_LANG']['PSA']['meetup_badge_meetup'] = 'Meetup';
$GLOBALS['TL_LANG']['PSA']['meetup_badge_post'] = 'Post';
$GLOBALS['TL_LANG']['PSA']['meetup_poll'] = 'Poll';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_votes'] = '%d votes';
$GLOBALS['TL_LANG']['PSA']['meetup_field_title'] = 'Title';
$GLOBALS['TL_LANG']['PSA']['meetup_field_title_placeholder'] = 'Evening walk by the harbor';
$GLOBALS['TL_LANG']['PSA']['meetup_field_description'] = 'Details';
$GLOBALS['TL_LANG']['PSA']['meetup_field_description_placeholder'] = 'Tell people when and where to meet…';
$GLOBALS['TL_LANG']['PSA']['meetup_field_date'] = 'When (optional)';
$GLOBALS['TL_LANG']['PSA']['meetup_field_location'] = 'Where (optional)';
$GLOBALS['TL_LANG']['PSA']['meetup_field_location_placeholder'] = 'Meeting point';
$GLOBALS['TL_LANG']['PSA']['meetup_create_submit'] = 'Post';
$GLOBALS['TL_LANG']['PSA']['meetup_login_required'] = 'Only members can create meetups and join. Please <a href="%s">log in</a> first.';
$GLOBALS['TL_LANG']['PSA']['meetup_feed_title'] = 'Community posts';
$GLOBALS['TL_LANG']['PSA']['meetup_feed_empty'] = 'No posts yet. Be the first!';
$GLOBALS['TL_LANG']['PSA']['meetup_when'] = 'When';
$GLOBALS['TL_LANG']['PSA']['meetup_where'] = 'Where';
$GLOBALS['TL_LANG']['PSA']['meetup_join'] = "I'll join";
$GLOBALS['TL_LANG']['PSA']['meetup_leave'] = 'Leave';
$GLOBALS['TL_LANG']['PSA']['meetup_join_count'] = '%d joining';
$GLOBALS['TL_LANG']['PSA']['meetup_join_count_short'] = 'coming';
$GLOBALS['TL_LANG']['PSA']['meetup_decline_count_short'] = 'not coming';
$GLOBALS['TL_LANG']['PSA']['meetup_join_up'] = 'I am coming';
$GLOBALS['TL_LANG']['PSA']['meetup_join_down'] = 'Not coming';
$GLOBALS['TL_LANG']['PSA']['meetup_rsvp_label'] = 'Your response';
$GLOBALS['TL_LANG']['PSA']['meetup_joiners'] = 'Joining';
$GLOBALS['TL_LANG']['PSA']['meetup_reactions_label'] = 'Reactions';
$GLOBALS['TL_LANG']['PSA']['meetup_comments'] = 'Comments';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_label'] = 'Add a comment';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_placeholder'] = 'Say you are interested or ask a question…';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_submit'] = 'Comment';
$GLOBALS['TL_LANG']['PSA']['meetup_delete'] = 'Delete';
$GLOBALS['TL_LANG']['PSA']['meetup_delete_confirm'] = 'Delete this post? This cannot be undone.';
$GLOBALS['TL_LANG']['PSA']['meetup_login_comment'] = 'Log in to join or comment. <a href="%s">Sign in</a>';

$GLOBALS['TL_LANG']['FMD']['psa_meetup'] = ['PSA Meetups', 'Member outing board with join and comments.'];

$GLOBALS['TL_LANG']['MOD']['psa_community'] = 'PSA Community';

$GLOBALS['TL_LANG']['MOD']['psa_meetups'] = ['Meetups', 'Manage member meetup posts and comments'];

$GLOBALS['TL_LANG']['tl_psa_meetup'] = [
    'title' => ['Title', 'Short title for the meetup'],
    'member_id' => ['Author', 'Member who created this meetup'],
    'description' => ['Description', 'Details about the outing'],
    'meetupDate' => ['Date & time', 'When the meetup takes place'],
    'location' => ['Location', 'Meeting point or area'],
    'postType' => ['Type', 'Meetup or simple post'],
    'pollQuestion' => ['Poll question', 'Optional member poll'],
    'published' => ['Publish', 'Hide unpublished posts on the frontend'],
];

$GLOBALS['TL_LANG']['tl_psa_meetup']['postTypeRef'] = [
    'meetup' => 'Meetup',
    'post' => 'Simple post',
];

$GLOBALS['TL_LANG']['tl_psa_meetup_comment'] = [
    'member_id' => ['Author', 'Member who wrote the comment'],
    'comment' => ['Comment', 'Comment text'],
    'published' => ['Publish comment', 'Hide unpublished comments on the frontend'],
];

$GLOBALS['TL_LANG']['tl_psa_meetup_join'] = [
    'member_id' => ['Member', 'Member who joined the meetup'],
];
