<?php
/****************************************************
 * helper methods:
 * debug(), writeTitle(), writeValue() and getValue()
 ****************************************************/
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
function getValue($var, $color='red')
{
    if (is_null($var)) {
        $str = 'NULL';
    } elseif (empty($var)) {
        $str = 'EMPTY';
    } else {
        $str = $var;
    }
    return '<span style="background-color: '.$color.'; color: white; border: 1px solid black;">'.$str.'</span>';
}

define('TABLE_PREFIX', 'mytable_');

$dbinfo = array(
    'hostspec' => 'host',
    'database' => 'dbname',
    'phptype'  => 'mysql',
    'username' => 'user',
    'password' => 'pwd'
);

$params = array(
    'langs_avail_table' => TABLE_PREFIX.'langs_avail',
    'lang_id_col'     => 'ID',
    'lang_name_col'   => 'name',
    'lang_meta_col'   => 'meta',
    'lang_errmsg_col' => 'error_text',
    'strings_tables'  => array(
                            'en' => TABLE_PREFIX.'i18n',
                            'it' => TABLE_PREFIX.'i18n',
                            'de' => TABLE_PREFIX.'i18n',
                            //'es' => TABLE_PREFIX.'i18n',
                            //'fr' => TABLE_PREFIX.'i18n'
                         ),
    'string_id_col'        => 'ID',
    'string_page_id_col'   => 'pageID',
    'string_text_col'      => '%s',
    //'prefetch' => false  //more queries, smaller result sets
                           //(use when db load is cheaper than network load)
);
/*
$cache_options = array(
    'cacheDir' => 'cache/',
    'lifeTime' => 3600*24 //one day
);
*/
?>