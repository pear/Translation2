<?php
// $Id$

require_once 'db_test_base.php';

class TestOfContainerMDB extends TestOfContainerDB {
    function TestOfContainerMDB($name='Test of Container MDB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB';
        $this->tr = Translation2::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfContainerMDB();
    $test->run(new HtmlReporter());
}
?>