<?php
define('TABLE_PREFIX', '');

$dbinfo = array(
    'hostspec' => 'host',
    'database' => 'dbname',
    'phptype'  => 'mysql',
    'username' => 'user',
    'password' => 'pwd'
);

$params = array(
    'langs_avail_table' => TABLE_PREFIX.'langs_avail',
    'lang_id_col'       => 'id',
    'lang_name_col'     => 'name',
    'lang_meta_col'     => 'meta',
    'lang_errmsg_col'   => 'error_text',
    'lang_encoding_col' => 'encoding',
    'strings_tables'    => array(
        'en' => TABLE_PREFIX.'i18n_en',
        'it' => TABLE_PREFIX.'i18n_it',
        'de' => TABLE_PREFIX.'i18n_de'
    ),
    'string_id_col'      => 'string_id',
    'string_page_id_col' => 'page_id',
    'string_text_col'    => 'string'
    //'prefetch' => false  //more queries, smaller result sets
                           //(use when db load is cheaper than network load)
);

$driver = 'MDB2';
