<?php
// $Id$

require_once 'dbms.php';

class TestOfAdminContainerDB extends UnitTestCase {
    var $tr;
    function TestOfAdminContainerDB($name='Test of Admin Container DB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'DB';
        $this->tr = Translation2_Admin::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
    function tearDown() {
        unset($this->tr);
    }
    function testFactory() {
        if (PEAR::isError($this->tr)) {
            var_dump($this->tr->getUserInfo());
            var_dump($this->tr->getMessage());
            //var_dump(debug_backtrace());
            exit;
        }
        $this->assertTrue(!PEAR::isError($this->tr));
    }
    function testAddLang() {
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
        $this->tr->addLang($langData);
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
        $this->assertTrue($this->tr->removeLang('fr'));
        $this->assertEqual($pre, $this->tr->getLangs('array'));
    }
    function testUpdateLang() {
        $original = array(
            'id'         => 'en',
            'name'       => 'english',
            'meta'       => 'my meta info',
            'error_text' => 'not available in English',
            'encoding'   => 'iso-8859-1',
        );
        $restore = array(
            'lang_id'    => 'en',
            'name'       => 'english',
            'meta'       => 'my meta info',
            'error_text' => 'not available in English',
            'encoding'   => 'iso-8859-1',
        );


        $newLangData = array(
            'lang_id'    => 'en',
            'name'       => 'english2',
            'meta'       => 'my other meta info',
            'error_text' => 'not available in English2',
            'encoding'   => 'iso-8859-15',
        );
        $expected = array(
            'id'         => 'en',
            'name'       => 'english2',
            'meta'       => 'my other meta info',
            'error_text' => 'not available in English2',
            'encoding'   => 'iso-8859-15',
        );

        $this->assertTrue($this->tr->updateLang($newLangData));
        $this->tr->setLang('en');
        $this->assertEqual($expected, $this->tr->getLang('en', 'array'));
        
        $this->assertTrue($this->tr->updateLang($restore));
        $this->assertEqual($original, $this->tr->getLang('en', 'array'));
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
    function testGetPageNames() {
        $expected = array(
            null,
            '',
            'alone',
            'bbcode',
            'calendar',
            'de',
            'in_page',
            'samplePage',
            'small page',
        );
        sort($expected);
        $actual = $this->tr->getPageNames();
        sort($actual);
        $this->assertEqual($expected, $actual);
    }
}
?>