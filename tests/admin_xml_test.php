<?php
// $Id$

require_once 'admin_db_test.php';

class TestOfAdminContainerXML extends TestOfAdminContainerDB {
    function TestOfAdminContainerXML($name='Test of Admin Container XML') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'XML';
        $options = array(
            'filename'         => 'i18n.xml',
            'save_on_shutdown' => false, //save in real time!
        );
        $this->tr =& Translation2_Admin::factory($driver, $options);
    }
    function tearDown() {
        unset($this->tr);
    }
    function testCreateNewLang() {
        $langData = array(
            'lang_id'    => 'fr',
            'name'       => 'français',
            'meta'       => '123 abc',
            'error_text' => 'non disponible',
            'encoding'   => 'iso-8859-1',
        );
        $pre = $this->tr->getLangs('array');
        // create a new language
        $this->tr->createNewLang($langData);
        $post = $this->tr->getLangs('array');
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
}
?>