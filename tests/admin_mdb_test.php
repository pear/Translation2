<?php
// $Id$

require_once 'admin_db_test_base.php';

class TestOfAdminContainerMDB extends TestOfAdminContainerDB {
    function TestOfAdminContainerMDB($name='Test of Admin Container MDB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB';
        $this->tr = Translation2_Admin::factory($driver, dbms::getDbInfo(), dbms::getParams());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfAdminContainerMDB();
    $test->run(new HtmlReporter());
}
?>