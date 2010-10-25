<?php
// $Id$

require_once 'admin_db_test_base.php';

class TestOfAdminContainerGettextPO extends TestOfAdminContainerDB {
    var $options = array(
        'prefetch'          => false,
        'langs_avail_file'  => 'gettext_langs.ini',
        'domains_path_file' => 'gettext_domains.ini',
        'default_domain'    => 'messages',
        'file_type'         => 'po',
        'carriage_return'   => "\n",
    );
    var $domains = array(
        'calendar'   => 'locale/',
        'alone'      => 'locale/',
        'admin'	     => 'locale/',
        'small page' => 'locale/',
        'messages'   => 'locale/',
        'in_page'    => 'locale/',
    );
    function TestOfAdminContainerGettextPO($name='Test of Admin Container Gettext PO') {
        $this->UnitTestCase($name);
    }
    
    function init() {
        $langs = array(
            'it' => array(
                'name'       => 'italiano',
                'meta'       => 'charset: iso-8859-1',
                'error_text' => 'non disponibile in Italiano',
                'encoding'   => 'iso-8859-1',
            ),
            'en' => array(
                'name'       => 'english',
                'meta'       => 'my meta info',
                'error_text' => 'not available in English',
                'encoding'   => 'iso-8859-1',
            ),
            'de' => array(
                'name'       => 'deutsch',
                'meta'       => 'charset: iso-8859-1',
                'error_text' => 'kein Text auf Deutsch verfügbar',
                'encoding'   => 'iso-8859-1',
            ),
        );
        $this->write_ini_file($langs, $this->options['langs_avail_file'], true);
        $this->write_ini_file($this->domains, $this->options['domains_path_file'], false);
    }
    
    function setUp() {
        $this->init();
        $driver = 'gettext';
        $this->tr = Translation2_Admin::factory($driver, $this->options);
    }

    /**
     * This function writes an array to a INI file,
     * pretty much like parse_ini_file(), only in reverse.
     *
     * @param array   $array    Data to write
     * @param string  $filename Destination file name
     * @param boolean $process_sections The source array is bidimensional,
     *                          and the first keys are the section names
     */
    function write_ini_file($array, $filename, $process_sections = false)
    {
        if (!is_resource($f = fopen($filename, 'w'))) {
            die('Ops... cannot write "'.$filename.'" file');
        }
        $CRLF = $this->options['carriage_return'];

        @flock($f, LOCK_EX);

        if ($process_sections) {
            foreach ($array as $id => $data) {
                fwrite($f, '['. $id .']'. $CRLF);
                foreach ($data as $k => $v) {
                    fwrite($f, $k . ' = ' . $v . $CRLF);
                }
                fwrite($f, $CRLF);
            }
        } else {
            foreach ($array as $id => $data) {
                fwrite($f, $id . ' = ' . $data . $CRLF);
            }
        }

        @flock($f, LOCK_UN);
        fclose($f);
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
        //with gettext, empty strings are replaced by the stringID
        $this->assertEqual('sample', $this->tr->get($stringID, $pageID, 'en'));
    }
    function testGetPageNames() {
        $expected = array_keys($this->domains);
        sort($expected);
        $actual = $this->tr->getPageNames();
        sort($actual);
        $this->assertEqual($expected, $actual);
    }
    function testRemovePage() {
        $this->testGetPageNames();
        $stringArray = array(
            'en' => 'sample',
            'it' => 'esempio',
            'de' => 'Beispiel',
        );

        //add new page
        $newpage = 'new page';
        $this->assertTrue($this->tr->add('sample', $newpage, $stringArray));
        
        $actual = $this->tr->getPageNames();
        $this->assertTrue(in_array($newpage, $this->tr->getPageNames()));

        //check if the page name was written in the .ini file
        $this->assertTrue($this->inifileHasDomain($newpage));
        
        //delete the page
        $this->assertTrue($this->tr->removePage($newpage));
        
        $this->testGetPageNames();
        
        //check if the page name was written in the .ini file
        $this->assertFalse($this->inifileHasDomain($newpage));
    }
    
    function inifileHasDomain($domain) {
        $inifile = parse_ini_file($this->options['domains_path_file']);
        return in_array($domain, array_keys($inifile));
    }
}
?>