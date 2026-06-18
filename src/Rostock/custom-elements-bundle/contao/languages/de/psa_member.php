<?php

$GLOBALS['TL_LANG']['PSA']['member_flash'] = [
    'registration_pending' => 'Danke für deine Registrierung! Wir haben dir einen Bestätigungslink per E-Mail geschickt. Bitte klicke auf den Link, um dein Konto zu aktivieren, und melde dich dann hier an.',
    'account_activated' => 'Dein Konto ist jetzt aktiv. Willkommen bei PSA Rostock — du kannst dich unten anmelden.',
];

$GLOBALS['TL_LANG']['PSA']['registration_email'] = <<<'TEXT'
Hallo ##firstname## ##lastname##,

danke für deine Registrierung bei PSA Rostock auf ##domain##.

Bitte klicke auf den folgenden Link, um deine E-Mail zu bestätigen und dein Konto zu aktivieren:

##link##

Der Bestätigungslink ist 1 Stunde gültig.

Wenn du kein Konto erstellt hast, ignoriere diese E-Mail bitte.

PSA Rostock
TEXT;
