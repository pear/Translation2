<?php
// $Id$

class dbms
{
    function getParams() {
        return array(
            'langs_avail_table' => 'langs_avail',
            'lang_id_col'     => 'id',
            'lang_name_col'   => 'name',
            'lang_meta_col'   => 'meta',
            'lang_errmsg_col' => 'error_text',
            'lang_encoding_col' => 'encoding',
            /*
            'strings_tables'  => array(
                                    'en' => 'i18n',
                                    'it' => 'i18n',
                                    'de' => 'i18n',
                                 ),
            */
            'strings_default_table' => 'i18n',
            'string_id_col'         => 'id',
            'string_page_id_col'    => 'page_id',
            'string_text_col'       => '%s',
            //'prefetch' => false  //more queries, smaller result sets
                                   //(use when db load is cheaper than network load)
        );
    }
    function getDbInfo() {
        return array(
            'hostspec' => 'localhost',
            'database' => 'translation2',
            'phptype'  => 'mysql',
            'username' => 'user',
            'password' => 'pass'
        );
    }
}
?>