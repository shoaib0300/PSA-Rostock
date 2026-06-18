<?php

$strTable = 'tl_quickbooker';

$GLOBALS['TL_LANG'][$strTable]['quickbooker_name'] = ['Handle QuickBooker Options', ''];
$GLOBALS['TL_LANG'][$strTable]['minStay'] = ['Minimum stay Days', 'Enter the minimum number of nights.'];
$GLOBALS['TL_LANG'][$strTable]['minStay'] = ['Minimum Stay Days', 'Specify the minimum stay in days.', 'Days'];
$GLOBALS['TL_LANG'][$strTable]['visibleAfter'] = ['Start Booking - After Days', 'Specify the number of days after which dates are visible.', 'Days'];
$GLOBALS['TL_LANG']['tl_quickbooker'] = [
    'seasonName' => ['Season Name', 'Name of the season'],
    'startDate'  => ['Start Date', 'Start date of the season fron 12 AM o\'clock in the mornig it is valid'],
    'endDate'    => ['End Date (this will count as a End date)', 'End date of the season until 23.59 PM o\'clock in the evening it is valid'],
    'minStay'    => ['Minimum Stay', 'Minimum nights required'],
    'published'  => ['Published', 'Publish this season'],
];

// Legends for the tl_quickbooker table
$GLOBALS['TL_LANG'][$strTable]['detail_legend'] = 'Detail settings';
$GLOBALS['TL_LANG'][$strTable]['season_legend'] = 'Season Settings';
$GLOBALS['TL_LANG'][$strTable]['status_legend'] = 'Publication Settings';
$GLOBALS['TL_LANG']['MOD']['quickbooker_module'] = ['QuickBooker Handler', ''];
$GLOBALS['TL_LANG']['MOD']['booking_management'] = ['Booking Management', 'Manage bookings and related settings'];
