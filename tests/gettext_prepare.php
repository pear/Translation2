<?php

require_once 'System.php';
require_once 'File/Gettext.php';
require_once 'I18Nv2/Locale.php';

$l = &new I18Nv2_Locale('en');

foreach (array('mo', 'po') as $fileType) {

    $g = &File_Gettext::factory($fileType);
    $g->meta = array('Content-Type' => 'text/plain; charset=iso-8859-1');
    
    // =============================================================================

    $langs = array('en', 'de', 'it');
    foreach ($langs as $lang) {
        $l->setLocale($lang);
        $g->strings = array();
        foreach (range(0, 6) as $day) {
            $g->strings["day_$day"] = strtolower($l->dayName($day));
        }
        foreach (range(0, 11) as $month) {
            $g->strings[sprintf('month_%02d', $month + 1)] = strtolower($l->monthName($month));
        }
        if (!is_dir('locale')) {
            mkdir('locale');
        }
        if (!is_dir('locale/' . $lang)) {
            mkdir('locale/' . $lang);
        }
        $dir = 'locale/'. $lang .'/LC_MESSAGES/';
        if (!is_dir($dir)) {
            mkdir($dir);
            //System::mkdir(array('-p', $dir = 'locale/'. $lang .'/LC_MESSAGES/'));
        }
        $g->save($dir . 'calendar.'.$fileType);
    }

    $g->strings = array('alone' => 'solo soletto');
    $g->save('locale/it/LC_MESSAGES/alone.'.$fileType);
    $g->strings = array('alone' => 'all alone');
    $g->save('locale/en/LC_MESSAGES/alone.'.$fileType);

    // =============================================================================

    $g->strings = array('prova_conflitto' => 'testo con conflitto - in page');
    $g->save('locale/it/LC_MESSAGES/in_page.'.$fileType);
    $g->strings = array('prova_conflitto' => 'conflicting text - in page');
    $g->save('locale/en/LC_MESSAGES/in_page.'.$fileType);

    // =============================================================================

    $g->strings = array(
        'only_english'    => null,
        'only_italian'    => 'testo solo in italiano',
        'hello_user'      => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
        'isempty'         => null,
        'prova_conflitto' => 'testo con conflitto - globale',
        'test'            => 'stringa di prova',
        'Entirely new string' => null,
    );
    $g->save('locale/it/LC_MESSAGES/messages.'.$fileType);
    $g->strings = array(
        'only_english'    => 'only english text',
        'only_italian'    => null,
        'hello_user'      => 'hello &&user&&, today is &&weekday&&, &&day&&th &&month&& &&year&&',
        'isempty'         => null,
        'prova_conflitto' => 'conflicting text - Global',
        'test'            => 'this is a test string',
        'Entirely new string' => 'Entirely new string',
    );
    $g->save('locale/en/LC_MESSAGES/messages.'.$fileType);

    $g->strings = array('isempty' => 'this string is empty in English and Italian, but not in German!');
    $g->save('locale/de/LC_MESSAGES/messages.'.$fileType);

    // =============================================================================

    $g->strings = array(
        'first string'  => 'first string',
        'second string' => 'second string',
    );
    $g->save('locale/en/LC_MESSAGES/small page.'.$fileType);

    $g->strings = array(
        'first string'  => 'prima stringa',
        'second string' => 'seconda stringa',
    );
    $g->save('locale/it/LC_MESSAGES/small page.'.$fileType);
    
    unset($g);
}
?>