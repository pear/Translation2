<?php
// $Id$

require_once 'dbms_test.php';

class TestOfAdminContainerDB extends TestOfContainerDBMS {
    var $tr;
    function TestOfAdminContainerDB($name='Test of Admin Container DB') {
        $this->UnitTestCase($name);
    }
    function _getParams() {
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
    function _getDbInfo() {
        return array(
            'hostspec' => 'localhost',
            'database' => 'translation2',
            'phptype'  => 'mysql',
            'username' => 'root',
            'password' => ''
        );
    }
    function setUp() {
        $driver = 'DB';
        $this->tr = Translation2_Admin::factory($driver, $this->_getDbInfo(), $this->_getParams());
    }
    function tearDown() {
        unset($this->tr);
    }
    function testCreateNewLang() {
        $langData = array(
            'lang_id'    => 'fr',
            'table_name' => 'i18n',
            'name'       => 'français',
            'meta'       => '123 abc',
            'error_text' => 'non disponible',
            'encoding'   => 'iso-8859-1',
        );
        $pre = $this->tr->getLangs('array');
        // create a new language
        $this->tr->createNewLang($langData);
        $post = $this->tr->getLangs('array');
        $expected = array(
            'id'         => 'fr',
            'name'       => 'français',
            'meta'       => '123 abc',
            'error_text' => 'non disponible',
            'encoding'   => 'iso-8859-1',
        );
        $this->assertEqual($expected, array_pop(array_diff_assoc($post, $pre)));
        // remove the new language
        $this->tr->removeLang('fr');
        $this->assertEqual($pre, $this->tr->getLangs('array'));
    }
    function testAddUpdateRemove() {
        $stringArray = array(
            'en' => 'sample',
            'it' => 'esempio',
            'de' => 'Beispiel',
        );
        //add
        $this->assertTrue($this->tr->add('sample', 'new page', $stringArray));
        $this->assertEqual($stringArray['en'], $this->tr->get('sample', 'new page', 'en'));
        $this->assertEqual($stringArray['it'], $this->tr->get('sample', 'new page', 'it'));
        $this->assertEqual($stringArray['de'], $this->tr->get('sample', 'new page', 'de'));
        
        //update
        $newStringArray = array('en' => 'example');
        $this->assertTrue($this->tr->update('sample', 'new page', $newStringArray));
        $this->assertEqual($newStringArray['en'], $this->tr->get('sample', 'new page', 'en'));
        $this->assertEqual($stringArray['it'], $this->tr->get('sample', 'new page', 'it'));
        $this->assertEqual($stringArray['de'], $this->tr->get('sample', 'new page', 'de'));
        
        //remove
        $this->assertTrue($this->tr->remove('sample', 'new page'));
        $this->assertEqual('', $this->tr->get('sample', 'new page', 'en'));
    }
}
?>