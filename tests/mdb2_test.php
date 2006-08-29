<?php
// $Id$

require_once 'db_test_base.php';

class TestOfContainerMDB2 extends TestOfContainerDB {
    function TestOfContainerMDB2($name='Test of Container MDB2') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB2';
        $this->tr = Translation2::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfContainerMDB2();
    $test->run(new HtmlReporter());
}
?>