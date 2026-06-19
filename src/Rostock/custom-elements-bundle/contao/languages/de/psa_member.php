<?php

$GLOBALS['TL_LANG']['PSA']['member_flash'] = [
    'registration_pending' => 'Danke für deine Registrierung! Wir haben dir einen Bestätigungslink per E-Mail geschickt. Bitte klicke auf den Link, um dein Konto zu aktivieren, und melde dich dann hier an.',
    'account_activated' => 'Dein Konto ist jetzt aktiv. Willkommen bei PSA Rostock — du kannst dich unten anmelden.',
    'password_reset_sent' => 'Wenn ein Konto mit dieser E-Mail existiert, haben wir dir einen Link zum Zurücksetzen des Passworts geschickt. Bitte prüfe dein Postfach.',
    'password_changed' => 'Dein Passwort wurde aktualisiert. Du kannst dich unten mit deinem neuen Passwort anmelden.',
];

$GLOBALS['TL_LANG']['PSA']['login_identifier'] = 'Benutzername oder E-Mail';

$GLOBALS['TL_LANG']['PSA']['account_edit'] = 'Bearbeiten';
$GLOBALS['TL_LANG']['PSA']['account_save'] = 'Speichern';
$GLOBALS['TL_LANG']['PSA']['account_cancel'] = 'Abbrechen';
$GLOBALS['TL_LANG']['PSA']['account_required_badge'] = 'Pflichtfeld';
$GLOBALS['TL_LANG']['PSA']['account_required_empty'] = 'Bitte ausfüllen';
$GLOBALS['TL_LANG']['PSA']['account_empty_value'] = '—';
$GLOBALS['TL_LANG']['PSA']['account_password_masked'] = '••••••••';
$GLOBALS['TL_LANG']['PSA']['account_avatar_set'] = 'Foto hochgeladen';
$GLOBALS['TL_LANG']['PSA']['account_posts_title'] = 'Meine Beiträge & Treffen';
$GLOBALS['TL_LANG']['PSA']['account_posts_show'] = 'Meine Beiträge & Treffen anzeigen';
$GLOBALS['TL_LANG']['PSA']['account_posts_hide'] = 'Meine Beiträge & Treffen ausblenden';
$GLOBALS['TL_LANG']['PSA']['account_posts_empty'] = 'Du hast noch keine Beiträge oder Treffen erstellt.';
$GLOBALS['TL_LANG']['PSA']['account_posts_create'] = 'Beitrag auf Meetups erstellen';
$GLOBALS['TL_LANG']['PSA']['account_posts_view'] = 'Auf Meetups ansehen';
$GLOBALS['TL_LANG']['PSA']['account_posts_draft'] = 'Entwurf';
$GLOBALS['TL_LANG']['PSA']['account_posts_count'] = '%s Eintrag';
$GLOBALS['TL_LANG']['PSA']['account_posts_count_plural'] = '%s Einträge';

$GLOBALS['TL_LANG']['PSA']['password_reset_email'] = <<<'TEXT'
Hallo ##firstname## ##lastname##,

du hast ein neues Passwort für dein PSA-Rostock-Konto auf ##domain## angefordert.

Bitte klicke auf den folgenden Link, um ein neues Passwort zu wählen:

##link##

Wenn du das nicht angefordert hast, ignoriere diese E-Mail bitte.

PSA Rostock
TEXT;

$GLOBALS['TL_LANG']['PSA']['registration_email'] = <<<'TEXT'
Hallo ##firstname## ##lastname##,

danke für deine Registrierung bei PSA Rostock auf ##domain##.

Bitte klicke auf den folgenden Link, um deine E-Mail zu bestätigen und dein Konto zu aktivieren:

##link##

Der Bestätigungslink ist 1 Stunde gültig.

Wenn du kein Konto erstellt hast, ignoriere diese E-Mail bitte.

PSA Rostock
TEXT;
