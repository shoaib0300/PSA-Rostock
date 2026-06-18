<?php

$strTable = 'tl_quickbooker';

$GLOBALS['TL_LANG'][$strTable]['quickbooker_name'] = ['QuickBooker-Optionen verwalten', ''];
$GLOBALS['TL_LANG'][$strTable]['minStay'] = ['Mindestaufenthalt in Tagen', 'Geben Sie die Mindestanzahl der Nächte ein.'];
$GLOBALS['TL_LANG'][$strTable]['minStay'] = ['Mindestaufenthalt in Tagen', 'Geben Sie den Mindestaufenthalt in Tagen an.', 'Tage'];
$GLOBALS['TL_LANG'][$strTable]['visibleAfter'] = ['Buchung starten - Nach Tagen', 'Geben Sie die Anzahl der Tage an, nach denen Termine sichtbar werden.', 'Tage'];
$GLOBALS['TL_LANG']['tl_quickbooker'] = [
    'seasonName' => ['Saison-Name', 'Name der Saison'],
    'startDate'  => ['Startdatum', 'Startdatum der Saison ab 12:00 Uhr morgens gültig'],
    'endDate'    => ['Enddatum (gilt als Enddatum)', 'Enddatum der Saison bis 23:59 Uhr abends gültig'],
    'minStay'    => ['Mindestaufenthalt', 'Mindestanzahl erforderlicher Nächte'],
    'published'  => ['Veröffentlicht', 'Diese Saison veröffentlichen'],
];

// Legenden für die tl_quickbooker Tabelle
$GLOBALS['TL_LANG'][$strTable]['detail_legend'] = 'Detail-Einstellungen';
$GLOBALS['TL_LANG']['MOD']['quickbooker_module'] = ['QuickBooker-Verwaltung', ''];
$GLOBALS['TL_LANG']['MOD']['booking_management'] = ['Buchungsverwaltung', 'Buchungen und zugehörige Einstellungen verwalten'];
$GLOBALS['TL_LANG'][$strTable]['season_legend'] = 'Saison-Einstellungen';
$GLOBALS['TL_LANG'][$strTable]['status_legend'] = 'Veröffentlichungs-Einstellungen';