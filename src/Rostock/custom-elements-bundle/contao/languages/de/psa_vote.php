<?php

$GLOBALS['TL_LANG']['PSA']['vote_empty'] = 'Aktuell läuft keine Abstimmung.';
$GLOBALS['TL_LANG']['PSA']['vote_login_required'] = 'Bitte melden Sie sich als Mitglied an, um abzustimmen.';
$GLOBALS['TL_LANG']['PSA']['vote_login_cta'] = 'Zum Abstimmen anmelden';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_ends'] = 'Abstimmung endet am %s';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_not_open'] = 'Abstimmung noch nicht geöffnet.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_starts'] = 'Beginn am %s.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_ended'] = 'Diese Abstimmung ist beendet.';
$GLOBALS['TL_LANG']['PSA']['vote_campaign_upcoming'] = 'Diese Abstimmung hat noch nicht begonnen.';
$GLOBALS['TL_LANG']['PSA']['vote_position'] = 'Position';
$GLOBALS['TL_LANG']['PSA']['vote_select_candidate'] = 'Kandidat/in wählen';
$GLOBALS['TL_LANG']['PSA']['vote_submit'] = 'Stimme abgeben';
$GLOBALS['TL_LANG']['PSA']['vote_change'] = 'Stimme ändern';
$GLOBALS['TL_LANG']['PSA']['vote_your_choice'] = 'Ihre Stimme';
$GLOBALS['TL_LANG']['PSA']['vote_results'] = 'Ergebnisse';
$GLOBALS['TL_LANG']['PSA']['vote_votes'] = '%d Stimmen';
$GLOBALS['TL_LANG']['PSA']['vote_total_ballots'] = '%d Mitglieder haben abgestimmt';
$GLOBALS['TL_LANG']['PSA']['vote_success'] = 'Ihre Stimme wurde gespeichert.';
$GLOBALS['TL_LANG']['PSA']['vote_already_voted'] = 'Sie haben in dieser Abstimmung bereits gewählt.';
$GLOBALS['TL_LANG']['PSA']['vote_incomplete'] = 'Bitte wählen Sie für jede Position eine/n Kandidat/in: %s';
$GLOBALS['TL_LANG']['PSA']['vote_hint_single'] = 'Klicken Sie auf eine Person, um sie auszuwählen, und geben Sie dann Ihre Stimme ab.';
$GLOBALS['TL_LANG']['PSA']['vote_hint_multi'] = 'Wählen Sie für jede Position eine Person aus und geben Sie dann Ihre Stimme ab.';
$GLOBALS['TL_LANG']['PSA']['vote_selected'] = 'Gewählt';

$GLOBALS['TL_LANG']['FMD']['psa_vote'] = ['PSA Abstimmung', 'Zeigt aktive Abstimmungen für Mitglieder.'];

$GLOBALS['TL_LANG']['MOD']['psa_vote_reasons'] = ['Abstimmungspositionen', 'Wiederverwendbare Positionen für Kampagnen'];
$GLOBALS['TL_LANG']['MOD']['psa_vote_campaigns'] = ['Abstimmungskampagnen', 'Kampagnen und Kandidaten verwalten'];
$GLOBALS['TL_LANG']['MOD']['psa_vote_ballots'] = ['Abstimmungsstimmen', 'Abgegebene Mitgliederstimmen'];

$GLOBALS['TL_LANG']['tl_psa_vote_reason'] = [
    'reason_legend' => 'Position',
    'publish_legend' => 'Veröffentlichung',
    'title' => ['Titel', 'Positions- oder Rollenbezeichnung (z. B. Vorsitz)'],
    'photo' => ['Bild', 'Optionales Symbol oder Bild für diese Position'],
    'description' => ['Beschreibung', 'Kurze Erklärung der Position'],
    'published' => ['Veröffentlichen', 'Position in Kampagnen auswählbar machen'],
];

$GLOBALS['TL_LANG']['tl_psa_vote_campaign'] = [
    'campaign_legend' => 'Kampagne',
    'publish_legend' => 'Veröffentlichung',
    'title' => ['Titel', 'Kampagnenname im Frontend'],
    'description' => ['Beschreibung', 'Einleitungstext für Wähler/innen'],
    'startDate' => ['Beginn', 'Abstimmungsstart im Kalender wählen (leer = sofort)'],
    'endDate' => ['Ende', 'Abstimmungsende im Kalender wählen (leer = kein Ende)'],
    'showResults' => ['Ergebnisse anzeigen', 'Wann Stimmenzahlen sichtbar sind'],
    'published' => ['Kampagne veröffentlichen', 'Kampagne im Frontend anzeigen'],
    'ballotCount' => '%d Stimmen',
    'statusRef' => [
        'draft' => 'Entwurf',
        'upcoming' => 'Geplant',
        'active' => 'Aktiv',
        'ended' => 'Beendet',
    ],
    'showResultsRef' => [
        'after_vote' => 'Nach eigener Stimmabgabe',
        'after_end' => 'Nach Kampagnenende',
        'always' => 'Immer',
        'never' => 'Nie (nur Admin)',
    ],
];

$GLOBALS['TL_LANG']['tl_psa_vote_candidate'] = [
    'candidate_legend' => 'Kandidat/in',
    'publish_legend' => 'Veröffentlichung',
    'reason_id' => ['Positionsvorlage', 'Gespeicherte Position wählen oder leer lassen und eigene Position eingeben'],
    'name' => ['Person', 'Name der/des Kandidat/in'],
    'photo' => ['Foto', 'Porträt der/des Kandidat/in'],
    'position' => ['Eigene Position', 'Wird verwendet, wenn keine Vorlage gewählt ist'],
    'description' => ['Kurztext', 'Kurzes Statement der/des Kandidat/in'],
    'published' => ['Kandidat/in veröffentlichen', 'Person in der Kampagne anzeigen'],
    'no_position' => 'Allgemein',
];

$GLOBALS['TL_LANG']['ERR']['voteEndBeforeStart'] = 'Das Enddatum muss am oder nach dem Startdatum liegen.';

$GLOBALS['TL_LANG']['tl_psa_vote_ballot'] = [
    'campaign_id' => ['Kampagne', ''],
    'reason_id' => ['Position', ''],
    'candidate_id' => ['Kandidat/in', ''],
    'member_id' => ['Mitglied', ''],
];
