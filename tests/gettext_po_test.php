<?php
// $Id$

require_once 'db_test.php';

class TestOfGettextPO extends TestOfContainerDB {
    function TestOfGettextPO($name='Test of Container gettext PO') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'gettext';
        $options = array(
            'prefetch'          => false,
            'langs_avail_file'  => 'gettext_langs.ini',
            'domains_path_file' => 'gettext_domains.ini',
            'default_domain'    => 'messages',
            'file_type'         => 'po',
        );
        $this->tr = Translation2::factory($driver, $options);
    }
    function testDefaultTextDecorator() {
        $this->tr->setLang('it');
        //without decorator
        //with gettext, empty strings are replaced by the stringID
        $this->assertEqual('isempty', $this->tr->get('isempty'));
        $expected = array(
            'only_english' => null,
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => null,
            'prova_conflitto' => 'testo con conflitto - globale',
            'test'         => 'stringa di prova',
            'Entirely new string' => null,
        );
        //with decorator
        $this->tr =& $this->tr->getDecorator('DefaultText');
        $this->assertEqual('isempty', $this->tr->get('isempty'));
        $this->assertEqual($expected, $this->tr->getRawPage());
        $expected = array(
            'only_english' => 'only_english',
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => 'isempty',
            'prova_conflitto' => 'testo con conflitto - globale',
            'test'         => 'stringa di prova',
            'Entirely new string' => 'Entirely new string',
        );
        $this->assertEqual($expected, $this->tr->getPage());
    }
    function testLangDecorator() {
        $this->tr->setLang('it');
        $this->tr =& $this->tr->getDecorator('Lang');
        $this->tr->setOption('fallbackLang', 'en');
        //with gettext, empty strings are replaced by the stringID
        $this->assertEqual('only_english', $this->tr->get('only_english'));
        $expected = array(
            'only_english' => null,
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => null,
            'prova_conflitto' => 'testo con conflitto - globale',
            'test'         => 'stringa di prova',
            'Entirely new string' => null,
        );
        $this->assertEqual($expected, $this->tr->getRawPage());
        $this->tr =& $this->tr->getDecorator('Lang');
        $this->tr->setOption('fallbackLang', 'de');
        $expected = array(
            'only_english' => 'only english text',
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => 'this string is empty in English and Italian, but not in German!',
            'test'         => 'stringa di prova',
            'prova_conflitto' => 'testo con conflitto - globale',
            'Entirely new string' => 'Entirely new string',
        );
        $this->assertEqual($expected, $this->tr->getPage());
    }
}
?>