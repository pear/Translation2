<?php
// $Id$

require_once 'db_test.php';

class TestOfContainerMDB extends TestOfContainerDB {
    function TestOfContainerMDB($name='Test of Container MDB') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'MDB';
        $this->tr = Translation2::factory($driver, $this->_getDbInfo(), $this->_getParams());
    }
}
?>