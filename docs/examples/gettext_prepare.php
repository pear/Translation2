<?php

require_once 'System.php';
require_once 'File/Gettext.php';
require_once 'I18Nv2/Locale.php';

$l = &new I18Nv2_Locale('en');
$g = &File_Gettext::factory('MO');

$langs = array('en', 'de', 'it');
foreach ($langs as $lang) {
    $l->setLocale($lang);
    $g->strings = array();
    foreach (range(0, 6) as $day) {
        $g->strings["day_$day"] = $l->dayName($day);
    }
    foreach (range(0, 11) as $month) {
        $g->strings[sprintf('month_%02d', $month + 1)] = $l->monthName($month);
    }
    System::mkdir(array('-p', $dir = 'locale/'. $lang .'/LC_MESSAGES/'));
    $g->save($dir . 'calendar.mo');
}
$g->strings = array('alone' => 'solo soletto');
$g->save('locale/it/LC_MESSAGES/alone.mo');

bindtextdomain('calendar', 'locale/');
textdomain('calendar');
echo implode(', ', array(_('month_01'), _('month_02'), _('month_03'), _('month_04'), _('month_05'), _('month_06'), _('month_07'), _('month_08'), _('month_09'), _('month_10'), _('month_11'), _('month_12')));
?>
