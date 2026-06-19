<?php

$GLOBALS['TL_LANG']['PSA']['member_flash'] = [
    'registration_pending' => 'Thank you for registering! We sent a confirmation link to your email address. Please click the link to activate your account, then log in here.',
    'account_activated' => 'Your account is now active. Welcome to PSA Rostock — you can log in below.',
    'password_reset_sent' => 'If an account exists for that e-mail address, we sent a password reset link. Please check your inbox.',
    'password_changed' => 'Your password has been updated. You can log in with your new password below.',
];

$GLOBALS['TL_LANG']['PSA']['login_identifier'] = 'Username or e-mail';

$GLOBALS['TL_LANG']['PSA']['account_edit'] = 'Edit';
$GLOBALS['TL_LANG']['PSA']['account_save'] = 'Save';
$GLOBALS['TL_LANG']['PSA']['account_cancel'] = 'Cancel';
$GLOBALS['TL_LANG']['PSA']['account_required_badge'] = 'Required';
$GLOBALS['TL_LANG']['PSA']['account_required_empty'] = 'Please fill in';
$GLOBALS['TL_LANG']['PSA']['account_empty_value'] = '—';
$GLOBALS['TL_LANG']['PSA']['account_password_masked'] = '••••••••';
$GLOBALS['TL_LANG']['PSA']['account_avatar_set'] = 'Photo uploaded';
$GLOBALS['TL_LANG']['PSA']['account_posts_title'] = 'My posts & meetups';
$GLOBALS['TL_LANG']['PSA']['account_posts_show'] = 'Show my posts & meetups';
$GLOBALS['TL_LANG']['PSA']['account_posts_hide'] = 'Hide my posts & meetups';
$GLOBALS['TL_LANG']['PSA']['account_posts_empty'] = 'You have not created any posts or meetups yet.';
$GLOBALS['TL_LANG']['PSA']['account_posts_create'] = 'Create a post on Meetups';
$GLOBALS['TL_LANG']['PSA']['account_posts_view'] = 'View on Meetups';
$GLOBALS['TL_LANG']['PSA']['account_posts_draft'] = 'Draft';
$GLOBALS['TL_LANG']['PSA']['account_posts_count'] = '%s item';
$GLOBALS['TL_LANG']['PSA']['account_posts_count_plural'] = '%s items';

$GLOBALS['TL_LANG']['PSA']['password_reset_email'] = <<<'TEXT'
Dear ##firstname## ##lastname##,

You requested a password reset for your PSA Rostock account on ##domain##.

Please click the link below to choose a new password:

##link##

If you did not request this, please ignore this e-mail.

PSA Rostock
TEXT;

$GLOBALS['TL_LANG']['PSA']['registration_email'] = <<<'TEXT'
Dear ##firstname## ##lastname##,

Thank you for registering with PSA Rostock on ##domain##.

Please click the link below to confirm your email and activate your account:

##link##

This confirmation link is valid for 1 hour.

If you did not create this account, please ignore this email.

PSA Rostock
TEXT;
