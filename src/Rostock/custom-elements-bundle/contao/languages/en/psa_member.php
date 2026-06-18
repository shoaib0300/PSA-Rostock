<?php

$GLOBALS['TL_LANG']['PSA']['member_flash'] = [
    'registration_pending' => 'Thank you for registering! We sent a confirmation link to your email address. Please click the link to activate your account, then log in here.',
    'account_activated' => 'Your account is now active. Welcome to PSA Rostock — you can log in below.',
];

$GLOBALS['TL_LANG']['PSA']['registration_email'] = <<<'TEXT'
Dear ##firstname## ##lastname##,

Thank you for registering with PSA Rostock on ##domain##.

Please click the link below to confirm your email and activate your account:

##link##

This confirmation link is valid for 1 hour.

If you did not create this account, please ignore this email.

PSA Rostock
TEXT;
