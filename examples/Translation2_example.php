<?php
/*************************************
 * helper methods:
 * debug(), writeTitle(), writeValue()
 *************************************/
function debug($str='')
{
    echo '<pre><div style="background-color: #ccffcc; border: 1px solid red; padding-left: 4px;">';
    print_r($str);
    echo '</div></pre>';
}
function writeTitle($str='')
{
    echo '<br /> <h2 style="padding: 5px; background-color: #ccccff; border: 1px solid black;">'.$str.'</h2>';
}
function writeValue($desc='', $var)
{
    echo '<div style="background-color: #f8f8f8; border: 1px solid #ccc; margin: 4px; padding: 4px;">'. $desc .' = ';
    var_dump($var);
    echo '</div>';
}


/**
 * require class, set parameters and options
 */
require_once 'Translation2/Translation2.php';

define('TABLE_PREFIX', 'mytable_');

$dbinfo = array(
    'hostspec' => 'myhost',
    'database' => 'mydb',
    'phptype'  => 'mysql',
    'username' => 'myuid',
    'password' => 'mypwd'
);

$params = array(
    'langs_avail_table' => TABLE_PREFIX.'langs_avail',
    'lang_id_col'     => 'ID',
    'lang_name_col'   => 'name',
    'lang_meta_col'   => 'meta',
    'lang_errmsg_col' => 'error_text',
    'strings_tables'  => array(
                            'en' => TABLE_PREFIX.'i18n',
                            'it' => TABLE_PREFIX.'i18n'
                         ),
    'string_id_col'        => 'ID',
    'string_page_id_col'   => 'pageID',
    'string_text_col'      => '%s',
    //'prefetch' => false  //more queries, smaller result sets
                           //(use when db load is cheaper than network load)
);


$tr = new Translation2('MDB', $dbinfo, $params);
if (PEAR::isError($tr)) {
    debug($tr);
}



writeTitle('ITALIANO');
$tr->setLang('it');
$tr->setLangFallback('en');
$tr->setPageID();
debug('setLang(\'it\');');
debug('setLangFallback(\'en\');');
debug('setPageID(); //get global strings');
debug('get(\'month_01\');');
writeValue('month_01', $tr->get('month_01'));
debug('get(\'only_english\'); //test fallback language');
writeValue('only_english', $tr->get('only_english'));
debug('getPage();');
writeValue('all the page', $tr->getPage());


writeTitle('GET LANG INFO');
debug('getLang(); //no langID => get current lang');
writeValue('[IT] LANG_NAME', $tr->getLang()); //no langID => get current lang
debug('getLang(\'it\', \'error_text\'); //use 2nd parameter to choose the lang info you need');
writeValue('[IT] LANG_ERRTXT', $tr->getLang('it', 'error_text'));
debug('getLang(\'it\', \'meta\'); //use 2nd parameter to choose the lang info you need');
writeValue('[EN] LANG_META', $tr->getLang('it', 'meta'));
debug('getLang(\'en\'); //default format is \'name\'');
writeValue('[EN] LANG_NAME', $tr->getLang('en'));
writeValue('[EN] LANG_ERRTXT', $tr->getLang('en', 'error_text'));
writeValue('[EN] LANG_META', $tr->getLang('en', 'meta'));



writeTitle('ENGLISH');
$tr->setLang('en');
$tr->setLangFallback('it');
debug('setLang(\'en\');');
debug('setLangFallback(\'it\');');
writeValue('month_01', $tr->get('month_01'));
debug('get(\'only_italian\'); //test fallback language');
writeValue('only_italian', $tr->get('only_italian'));
writeValue('all the page', $tr->getPage());


writeTitle('TEST PARAMETER SUBSTITUTION');
$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => $tr->get('month_01', '', 'en'),
    'year'    => '2004',
    'weekday' => $tr->get('day_5', '', 'en')
));
writeValue('[EN] hello, user', $tr->get('hello_user'));

$tr->setLang('it');
$tr->setLangFallback('en');

$tr->setParams(array(
    0         => '',
    'user'    => 'Joe',
    'day'     => '15',
    'month'   => $tr->get('month_01', '', 'it'),
   'year'    => '2004',
    'weekday' => $tr->get('day_5', '', 'it')
));
writeValue('[IT] hello, user', $tr->get('hello_user'));

writeTitle('TRANSLATION (STRING TO STRING)');
writeValue('gennaio', $tr->translate('gennaio', 'en'));

writeTitle('TEST STRINGS WITH pageID NOT NULL');
writeValue('[IT] alone', $tr->get('alone', 'alone'));
$tr->setLang('en');
$tr->setLangFallback('it');
writeValue('[EN] alone', $tr->get('alone', 'alone'));

writeTitle('Use error_text when default and fallback lang and defaultText are EMPTY');
writeValue('[EN] empty string', $tr->get('isempty'));

$tr->setPageID('in_page');
writeTitle('HANDLE CONFLICTS');
writeValue('[EN] (in page) string', $tr->get('prova_conflitto'));     //pageID=null  => get current pageID
writeValue('[EN] (global)  string', $tr->get('prova_conflitto', '')); //pageID=''    => get strings with no pageID
writeValue('[EN] (in page) string', $tr->get('prova_conflitto', 'in_page'));  // => force pageID


writeTitle('DEBUG INFO');
debug('NUMBER OF DB QUERIES: '.$tr->storage->_queries);
?>