<?php

$GLOBALS['TL_LANG']['PSA']['meetup_page_title'] = 'Treffen';
$GLOBALS['TL_LANG']['PSA']['meetup_page_intro'] = 'Plane ein Treffen und lade andere Mitglieder ein, mitzukommen.';
$GLOBALS['TL_LANG']['PSA']['meetup_create_title'] = 'Beitrag erstellen';
$GLOBALS['TL_LANG']['PSA']['meetup_create_open'] = 'Treffen erstellen';
$GLOBALS['TL_LANG']['PSA']['meetup_modal_close'] = 'Schließen';
$GLOBALS['TL_LANG']['PSA']['meetup_modal_cancel'] = 'Abbrechen';
$GLOBALS['TL_LANG']['PSA']['meetup_type_label'] = 'Was möchtest du teilen?';
$GLOBALS['TL_LANG']['PSA']['meetup_type_meetup'] = 'Treffen';
$GLOBALS['TL_LANG']['PSA']['meetup_type_post'] = 'Einfacher Beitrag';
$GLOBALS['TL_LANG']['PSA']['meetup_add_poll'] = 'Umfrage für Mitglieder hinzufügen';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_question'] = 'Umfragefrage';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_question_placeholder'] = 'Welcher Tag passt am besten?';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_options'] = 'Antworten';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_option_placeholder'] = 'Option';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_add_option'] = '+ Option hinzufügen';
$GLOBALS['TL_LANG']['PSA']['meetup_badge_meetup'] = 'Treffen';
$GLOBALS['TL_LANG']['PSA']['meetup_badge_post'] = 'Beitrag';
$GLOBALS['TL_LANG']['PSA']['meetup_poll'] = 'Umfrage';
$GLOBALS['TL_LANG']['PSA']['meetup_poll_votes'] = '%d Stimmen';
$GLOBALS['TL_LANG']['PSA']['meetup_field_title'] = 'Titel';
$GLOBALS['TL_LANG']['PSA']['meetup_field_title_placeholder'] = 'Abendspaziergang am Hafen';
$GLOBALS['TL_LANG']['PSA']['meetup_field_description'] = 'Details';
$GLOBALS['TL_LANG']['PSA']['meetup_field_description_placeholder'] = 'Sag den Leuten, wann und wo ihr euch trefft…';
$GLOBALS['TL_LANG']['PSA']['meetup_field_date'] = 'Wann (optional)';
$GLOBALS['TL_LANG']['PSA']['meetup_field_location'] = 'Wo (optional)';
$GLOBALS['TL_LANG']['PSA']['meetup_field_location_placeholder'] = 'Treffpunkt';
$GLOBALS['TL_LANG']['PSA']['meetup_create_submit'] = 'Veröffentlichen';
$GLOBALS['TL_LANG']['PSA']['meetup_login_required'] = 'Nur Mitglieder können Treffen erstellen und teilnehmen. Bitte <a href="%s">melde dich an</a>.';
$GLOBALS['TL_LANG']['PSA']['meetup_feed_title'] = 'Community-Beiträge';
$GLOBALS['TL_LANG']['PSA']['meetup_feed_empty'] = 'Noch keine Beiträge. Sei der Erste!';
$GLOBALS['TL_LANG']['PSA']['meetup_when'] = 'Wann';
$GLOBALS['TL_LANG']['PSA']['meetup_where'] = 'Wo';
$GLOBALS['TL_LANG']['PSA']['meetup_join'] = 'Ich bin dabei';
$GLOBALS['TL_LANG']['PSA']['meetup_leave'] = 'Absagen';
$GLOBALS['TL_LANG']['PSA']['meetup_join_count'] = '%d nehmen teil';
$GLOBALS['TL_LANG']['PSA']['meetup_joiners'] = 'Teilnehmer';
$GLOBALS['TL_LANG']['PSA']['meetup_comments'] = 'Kommentare';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_label'] = 'Kommentar hinzufügen';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_placeholder'] = 'Sag, dass du dabei bist, oder stelle eine Frage…';
$GLOBALS['TL_LANG']['PSA']['meetup_comment_submit'] = 'Kommentieren';
$GLOBALS['TL_LANG']['PSA']['meetup_login_comment'] = 'Zum Mitmachen oder Kommentieren bitte <a href="%s">anmelden</a>.';

$GLOBALS['TL_LANG']['FMD']['psa_meetup'] = ['PSA Treffen', 'Mitglieder-Treffen mit Teilnahme und Kommentaren.'];

$GLOBALS['TL_LANG']['MOD']['psa_community'] = 'PSA Community';

$GLOBALS['TL_LANG']['MOD']['psa_meetups'] = ['Treffen', 'Mitglieder-Treffen und Kommentare verwalten'];

$GLOBALS['TL_LANG']['tl_psa_meetup'] = [
    'title' => ['Titel', 'Kurzer Titel des Treffens'],
    'member_id' => ['Autor', 'Mitglied, das das Treffen erstellt hat'],
    'description' => ['Beschreibung', 'Details zum Treffen'],
    'meetupDate' => ['Datum & Uhrzeit', 'Wann das Treffen stattfindet'],
    'location' => ['Ort', 'Treffpunkt oder Gegend'],
    'postType' => ['Typ', 'Treffen oder einfacher Beitrag'],
    'pollQuestion' => ['Umfragefrage', 'Optionale Mitglieder-Umfrage'],
    'published' => ['Veröffentlichen', 'Unveröffentlichte Beiträge im Frontend ausblenden'],
];

$GLOBALS['TL_LANG']['tl_psa_meetup']['postTypeRef'] = [
    'meetup' => 'Treffen',
    'post' => 'Einfacher Beitrag',
];

$GLOBALS['TL_LANG']['tl_psa_meetup_comment'] = [
    'member_id' => ['Autor', 'Mitglied, das den Kommentar geschrieben hat'],
    'comment' => ['Kommentar', 'Kommentartext'],
    'published' => ['Kommentar veröffentlichen', 'Unveröffentlichte Kommentare im Frontend ausblenden'],
];

$GLOBALS['TL_LANG']['tl_psa_meetup_join'] = [
    'member_id' => ['Mitglied', 'Mitglied, das am Treffen teilnimmt'],
];
