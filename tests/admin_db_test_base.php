<?php
// $Id$

require_once 'simple_include.php';
require_once 'translation2_admin_include.php';
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
            'name'       => 'fran�ais',
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
            'lang_id'    => 'fr',
            'name'       => 'fran�ais',
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
            'lang_id'    => 'en',
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
            'lang_id'    => 'en',
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
        $stringID = 'sample';
        $pageID   = 'new page';
        $stringArray = array(
            'en' => 'sample',
            'it' => 'esempio',
            'de' => 'Beispiel',
        );
        //add
        $this->assertTrue($this->tr->add($stringID, $pageID, $stringArray));
        $this->assertEqual($stringArray['en'], $this->tr->get($stringID, $pageID, 'en'));
        $this->assertEqual($stringArray['it'], $this->tr->get($stringID, $pageID, 'it'));
        $this->assertEqual($stringArray['de'], $this->tr->get($stringID, $pageID, 'de'));
        
        //update
        $newStringArray = array('en' => 'example');
        $this->assertTrue($this->tr->update($stringID, $pageID, $newStringArray));
        $this->assertEqual($newStringArray['en'], $this->tr->get($stringID, $pageID, 'en'));
        $this->assertEqual($stringArray['it'],    $this->tr->get($stringID, $pageID, 'it'));
        $this->assertEqual($stringArray['de'],    $this->tr->get($stringID, $pageID, 'de'));
        
        //remove
        $this->assertTrue($this->tr->remove($stringID, $pageID));
        $this->assertEqual('', $this->tr->get($stringID, $pageID, 'en'));
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

    function testRemovePage() {
        $stringID = 'sample';
        $pageID   = 'new page';

        $this->assertFalse(in_array($pageID, $this->tr->getPageNames()));

        $stringArray = array(
            'en' => 'sample',
            'it' => 'esempio',
            'de' => 'Beispiel',
        );
        //add new page
        $this->assertTrue($this->tr->add($stringID, $pageID, $stringArray));
        
        $this->assertEqual($stringArray['en'], $this->tr->get($stringID, $pageID, 'en'));
        $this->assertEqual($stringArray['it'],    $this->tr->get($stringID, $pageID, 'it'));
        $this->assertEqual($stringArray['de'],    $this->tr->get($stringID, $pageID, 'de'));

        $actual = $this->tr->getPageNames();
        $this->assertTrue(in_array($pageID, $this->tr->getPageNames()));

        //delete the page
        $this->assertTrue($this->tr->removePage($pageID));
        
        $this->assertEqual('', $this->tr->get($stringID, $pageID, 'en'));

        $this->assertFalse(in_array($pageID, $this->tr->getPageNames()));
    }
}
?>