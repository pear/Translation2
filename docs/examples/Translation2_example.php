<?php
/**
 * require file with settings and Translation2 class,
 * set parameters and options
 */
require_once './settings.php';
require_once 'Translation2.php';

//require_once 'Translation2/Admin.php';
//$tr = new Translation2_Admin($driver, $dbinfo, $params);

$tr = new Translation2($driver, $dbinfo, $params);
if (PEAR::isError($tr)) {
    debug($tr);
}

writeTitle('ITALIANO');
$tr->setLang('it');
$tr->setPageID();
$tr =& $tr->getDecorator('CacheMemory');
//$tr->setOption('prefetch', false);
$tr =& $tr->getDecorator('Lang');
$tr->setOption('fallbackLang', 'en');
$tr =& $tr->getDecorator('Lang');
$tr->setOption('fallbackLang', 'de');


// =[DEBUG INFO]======================================
$str = <<<EOT
// new Translation2 instance
// (look at settings.php for an example of \$dbinfo and \$params)
\$tr = new Translation2("\$driver", \$dbinfo, \$params);

// set Italian as default lang
\$tr->setLang('it');

// get global strings (pageID = NULL)
\$tr->setPageID();

// add a 'CacheMemory Decorator', i.e. add a memory-cache layer
// to avoid multiple queries to the db
\$tr = & \$tr->getDecorator('CacheMemory');

// set an 'English Decorator', i.e. add English as a fallback language
\$tr = & \$tr->getDecorator('Lang');
\$tr->setOption('fallbackLang', 'en');

// add a 'German Decorator', i.e. add German as a third fallback language
\$tr = & \$tr->getDecorator('Lang');
\$tr->setOption('fallbackLang', 'en');
EOT;
// ====================================================
debug($str);


debug('$tr->get(\'test\');');
writeValue('test', $tr->get('test'));
debug('$tr->get(\'only_english\'); //test fallback language for a string not translated in Italian');
writeValue('only_english', $tr->get('only_english'));
debug('$tr->getRawPage();');
writeValue('all the page (raw)', $tr->getRawPage());
debug('$tr->getPage();');
writeValue('all the page (with fallback langs)', $tr->getPage());


//-------------------------------------------------------

writeTitle('GET LANG INFO');
debug('$tr->getLang(); //no langID => get current lang');
writeValue('[IT] LANG_NAME', $tr->getLang()); //no langID => get current lang
debug('$tr->getLang(\'it\', \'error_text\'); //use 2nd parameter to choose the lang info you need');
writeValue('[IT] LANG_ERRTXT', $tr->getLang('it', 'error_text'));
debug('$tr->getLang(\'it\', \'meta\'); //use 2nd parameter to choose the lang info you need');
writeValue('[EN] LANG_META', $tr->getLang('it', 'meta'));
debug('$tr->getLang(\'en\'); //default format is \'name\'');
writeValue('[EN] LANG_NAME', $tr->getLang('en'));
debug('$tr->getLang(\'en\', \'error_text\');');
writeValue('[EN] LANG_ERRTXT', $tr->getLang('en', 'error_text'));
debug('$tr->getLang(\'en\', \'meta\');');
writeValue('[EN] LANG_META', $tr->getLang('en', 'meta'));


//-------------------------------------------------------


writeTitle('DEBUG INFO');
debug('NUMBER OF DB QUERIES: '.$tr->storage->_queries);
unset($tr);


//-------------------------------------------------------

//new example

writeTitle('ENGLISH');
$tr = new Translation2($driver, $dbinfo, $params);
$tr->setLang('en');
$tr->setPageID();
$tr = & $tr->getDecorator('CacheMemory');
//$tr->prefetch = false;
$tr = & $tr->getDecorator('Lang');
$tr->setOption('fallbackLang', 'it');


// =[DEBUG INFO]======================================
$str = <<<EOT
// new Translation2 instance
\$tr = new Translation2("\$driver", \$dbinfo, \$params);

// set English as default lang
\$tr->setLang('en');

// get global strings (empty pageID)
\$tr->setPageID();

// add a 'CacheMemory Decorator', i.e. add a memory-cache layer
// to avoid multiple queries to the db
\$tr = & \$tr->getDecorator('CacheMemory');

// set an 'Italian Decorator', i.e. add Italian as a fallback language
\$tr = & \$tr->getDecorator('Lang');
\$tr->setOption('fallbackLang', 'it');
EOT;
// ====================================================
debug($str);


debug('$tr->get(\'test\');');
writeValue('test', $tr->get('test'));
debug('get(\'only_italian\'); //test fallback language for a string not translated in English');
writeValue('only_italian', $tr->get('only_italian'));
debug('getRawPage();');
writeValue('all the page (raw)', $tr->getRawPage());
debug('getPage();');
writeValue('all the page (with fallback langs)', $tr->getPage());


//-------------------------------------------------------


writeTitle('TEST PARAMETER SUBSTITUTION');
$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => $tr->get('month_01', 'calendar', 'en'),
    'year'    => '2004',
    'weekday' => $tr->get('day_5', 'calendar', 'en')
));
// =[DEBUG INFO]======================================
$str = <<<EOT
\$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => \$tr->get('month_01', 'calendar', 'en'),
    'year'    => '2004',
    'weekday' => \$tr->get('day_5', 'calendar', 'en')
));
EOT;
// ====================================================
debug($str);

debug('$tr->get(\'hello_user\');');
writeValue('[EN] hello, user', $tr->get('hello_user'));



$tr->setLang('it');
$tr->setOption('fallbackLang', 'en');
$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => $tr->get('month_01', 'calendar'),
    'year'    => '2004',
    'weekday' => $tr->get('day_5', 'calendar')
));
// =[DEBUG INFO]======================================
$str = <<<EOT
\$tr->setLang('it');
\$tr->setOption('fallbackLang', 'en');
\$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => \$tr->get('month_01', 'calendar', 'it'),
    'year'    => '2004',
    'weekday' => \$tr->get('day_5', 'calendar', 'it')
));
EOT;
// ====================================================
debug($str);
writeValue('[IT] hello, user', $tr->get('hello_user'));


//-------------------------------------------------------


writeTitle('SPECIAL CHARS DECORATOR');
$tr = & $tr->getDecorator('SpecialChars');

// =[DEBUG INFO]======================================
$str = <<<EOT
// set a 'SpecialChars Decorator' to replace htmlentities
\$tr = & Translation2->getDecorator('SpecialChars');
\$tr->setOptions(array('charset' => 'ISO-8859-1'); //default
EOT;
// ====================================================
debug($str);
debug('$tr->get(\'day_5\', \'calendar\', \'it\');');

writeValue('venerdì', $tr->get('day_5', 'calendar', 'it'));


//-------------------------------------------------------


writeTitle('TRANSLATION (STRING TO STRING)');
debug('$tr->translate(\'gennaio\', \'en\', \'calendar\');');
writeValue('gennaio', $tr->translate('gennaio', 'en', 'calendar'));





//-------------------------------------------------------


writeTitle('TEST STRINGS WITH pageID NOT NULL');
debug('$tr->get(\'alone\', \'alone\');');
writeValue('[IT] alone', $tr->get('alone', 'alone'));
debug('$tr->get(\'alone\', \'alone\', \'en\');');
writeValue('[EN] alone', $tr->get('alone', 'alone', 'en'));


//-------------------------------------------------------


writeTitle('HANDLE CONFLICTS');
$tr->setLang('en');
$tr->setOption('fallbackLang', 'it');
$tr->setPageID('in_page');

// =[DEBUG INFO]======================================
$str = <<<EOT
\$tr->setLang('en');
\$tr->setOption('fallbackLang', 'it');
\$tr->setPageID('in_page');
EOT;
// ====================================================
debug($str);

debug('$tr->get(\'prova_conflitto\'); //pageID=TRANSLATION2_DEFAULT_PAGEID => get current pageID');
writeValue('[EN] (in page) string', $tr->get('prova_conflitto'));
debug('$tr->get(\'prova_conflitto\', null); //pageID=null => get strings with pageID = NULL');
writeValue('[EN] (global)  string', $tr->get('prova_conflitto', null));
debug('$tr->get(\'prova_conflitto\', \'in_page\'); //force pageID');
writeValue('[EN] (in page) string', $tr->get('prova_conflitto', 'in_page'));


//-------------------------------------------------------


writeTitle('USE A DefaultText DECORATOR TO DEAL WITH EMPTY STRINGS');
$tr = & $tr->getDecorator('DefaultText');

// =[DEBUG INFO]======================================
$str = <<<EOT
\$tr = & \$tr->getDecorator('DefaultText');
EOT;
// ====================================================
debug($str);

debug('$tr->get(\'isempty\'); //get stringID when the string is empty');
writeValue('[EN] empty string', $tr->get('isempty'));

debug('$tr->get(\'isempty\', null, \'en\', \'show this default text\'); //use a custom fallback text');
writeValue('[EN] empty string', $tr->get('isempty', null, 'en', 'show this default text'));




/*
writeTitle('Use error_text when default and fallback lang and defaultText are EMPTY');
writeValue('[EN] empty string', $tr->get('isempty'));
*/



if (strtolower(get_class($tr)) == 'translation2_admin') {

    writeTitle('TEST ADMIN');
    $res = $tr->add('smallTest', null, array('it' => 'piccolo test',
                                             'en' => 'small test')
            );
    writeValue('add(smallTest)', $res);

    $res = $tr->add('smallTest', null, array('de' => 'kinder'));
    writeValue('add(smallTest)', $res);

    $res = $tr->remove('smallTest', null);
    writeValue('remove(smallTest)', $res);
}


writeTitle('DEBUG INFO');
debug('NUMBER OF DB QUERIES: '.$tr->storage->_queries);
?>