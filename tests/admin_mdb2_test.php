<?php
// $Id$

require_once 'admin_db_test.php';

class TestOfAdminContainerMDB2 extends TestOfAdminContainerDB {
    function TestOfAdminContainerMDB2($name='Test of Admin Container MDB2') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $driver = 'DB';
        $this->tr = Translation2_Admin::factory($driver, $this->_getDbInfo(), $this->_getParams());
    }
}
?>