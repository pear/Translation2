<?php
// $Id$

require_once 'dbms_test.php';

class TestOfContainerDB extends TestOfContainerDBMS {
    var $tr;
    function TestOfContainerDB($name='Test of Container DB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'DB';
        $this->tr = Translation2::factory($driver, $this->_getDbInfo(), $this->_getParams());
    }
    function tearDown() {
        unset($this->tr);
    }
    function testGet() {
        $this->assertEqual('gennaio', $this->tr->get('month_01', 'calendar', 'it'));
        $this->assertEqual('january', $this->tr->get('month_01', 'calendar', 'en'));
        $this->tr->setLang('en');         //set default lang
        $this->assertEqual('sunday', $this->tr->get('day_0', 'calendar'));
        $this->assertEqual('monday', $this->tr->get('day_1', 'calendar'));
        $this->tr->setPageID('calendar'); //set default lang AND default pageID
        $this->assertEqual('sunday', $this->tr->get('day_0'));
        $this->assertEqual('monday', $this->tr->get('day_1'));
    }
    function testGetPage() {
        $this->tr->setLang('en');
        $expected = array(
            'first string'  => 'first string',
            'second string' => 'second string',
        );
        $this->assertEqual($expected, $this->tr->getPage('small page'));
        $this->tr->setLang('it');
        $expected = array(
            'first string'  => 'prima stringa',
            'second string' => 'seconda stringa',
        );
        $this->assertEqual($expected, $this->tr->getPage('small page'));
    }
    function testGetRawPage() {
        $expected = array(
            'first string'  => 'first string',
            'second string' => 'second string',
        );
        $this->assertEqual($expected, $this->tr->getRawPage('small page', 'en'));
        $expected = array(
            'first string'  => 'prima stringa',
            'second string' => 'seconda stringa',
        );
        $this->assertEqual($expected, $this->tr->getRawPage('small page', 'it'));
    }
    function testConficts() {
        $this->tr->setLang('en');
        $this->tr->setPageID('in_page');
        //pageID=TRANSLATION2_DEFAULT_PAGEID => get current pageID
        $this->assertEqual('conflicting text - in page', $this->tr->get('prova_conflitto'));
        //pageID=null => get strings with pageID = NULL
        $this->assertEqual('conflicting text - Global', $this->tr->get('prova_conflitto', null));
        //force pageID
        $this->assertEqual('conflicting text - in page', $this->tr->get('prova_conflitto', 'in_page'));
    }
    function testParameterSubstitution() {
        $this->tr->setLang('en');
        $this->tr->setParams(array(
            'user'    => 'Joe',
            'day'     => '15',
            'month'   => $this->tr->get('month_01', 'calendar'),
            'year'    => '2004',
            'weekday' => $this->tr->get('day_5',    'calendar')
        ));
        $expected = 'hello Joe, today is friday, 15th january 2004';
        $this->assertEqual($expected, $this->tr->get('hello_user'));
        $this->tr->setLang('it');
        $this->tr->setParams(array(
            'user'    => 'Joe',
            'day'     => '15',
            'month'   => $this->tr->get('month_01', 'calendar'),
            'year'    => '2004',
            'weekday' => $this->tr->get('day_5',    'calendar')
        ));
        $expected = 'ciao, Joe, oggi è il 15 gennaio 2004 (venerdì)';
        $this->assertEqual($expected, $this->tr->get('hello_user'));
    }
    function testGetStringID() {
        $this->tr->setLang('en');
        $this->tr->setPageID('calendar');
        $this->assertEqual('month_01', $this->tr->getStringID('january'));
        $this->tr->setLang('it');
        $this->assertEqual('month_01', $this->tr->getStringID('gennaio'));
    }
    function testGetLang() {
        $this->tr->setLang('en');
        $this->assertEqual('english', $this->tr->getLang());
        $this->assertEqual('iso-8859-1',   $this->tr->getLang('en', 'encoding'));
        $this->assertEqual('my meta info', $this->tr->getLang('en', 'meta'));
        $expected = array(
            'id'         => 'it',
            'name'       => 'italiano',
            'meta'       => 'charset=iso-8859-1',
            'error_text' => 'non disponibile in Italiano',
            'encoding'   => 'iso-8859-1',
        );
        $this->assertEqual($expected, $this->tr->getLang('it', 'array'));
    }
    function testGetLangs() {
        $expected = array(
            'it',
            'en',
            'de',
        );
        $this->assertEqual($expected, $this->tr->getLangs('ids'));
        $expected = array(
            'it' => 'italiano',
            'en' => 'english',
            'de' => 'deutsch',
        );
        $this->assertEqual($expected, $this->tr->getLangs('names'));
        $expected = array(
            'it' => array(
                'id'         => 'it',
                'name'       => 'italiano',
                'meta'       => 'charset=iso-8859-1',
                'error_text' => 'non disponibile in Italiano',
                'encoding'   => 'iso-8859-1',
            ),
            'en' => array(
                'id'         => 'en',
                'name'       => 'english',
                'meta'       => 'my meta info',
                'error_text' => 'not available in English',
                'encoding'   => 'iso-8859-1',
            ),
            'de' => array(
                'id'         => 'de',
                'name'       => 'deutsch',
                'meta'       => 'charset=iso-8859-1',
                'error_text' => 'kein Text auf Deutsch verfügbar',
                'encoding'   => 'iso-8859-1',
            ),
        );
        $this->assertEqual($expected, $this->tr->getLangs('array'));
    }
    function testDefaultTextDecorator() {
        $this->tr->setLang('it');
        //without decorator
        $this->assertEqual('', $this->tr->get('isempty'));
        $expected = array(
            'only_english' => null,
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => null,
            'test'         => 'stringa di prova',
            'prova_conflitto' => 'testo con conflitto - globale',
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
            'test'         => 'stringa di prova',
            'prova_conflitto' => 'testo con conflitto - globale',
            'Entirely new string' => 'Entirely new string',
        );
        $this->assertEqual($expected, $this->tr->getPage());
    }
    function testLangDecorator() {
        $this->tr->setLang('it');
        $this->tr =& $this->tr->getDecorator('Lang');
        $this->tr->setOption('fallbackLang', 'en');
        $this->assertEqual('only english text', $this->tr->get('only_english'));
        $expected = array(
            'only_english' => null,
            'only_italian' => 'testo solo in italiano',
            'hello_user'   => 'ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)',
            'isempty'      => null,
            'test'         => 'stringa di prova',
            'prova_conflitto' => 'testo con conflitto - globale',
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
    function testIconvDecorator() {
        $this->assertEqual('venerdì', $this->tr->get('day_5', 'calendar', 'it'));
        $this->tr =& $this->tr->getDecorator('Iconv');
        $this->tr->setOptions(array('encoding' => 'UTF-8'));
        $this->assertEqual('venerdÃ¬', $this->tr->get('day_5', 'calendar', 'it'));
    }
    function testSpecialCharsDecorator() {
        $this->assertEqual('venerdì', $this->tr->get('day_5', 'calendar', 'it'));
        $this->tr =& $this->tr->getDecorator('SpecialChars');
        $this->assertEqual('venerd&igrave;', $this->tr->get('day_5', 'calendar', 'it'));
    }
}
?>