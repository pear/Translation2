<?php
// $Id$

require_once 'simple_include.php';
require_once 'translation2_admin_include.php';

class AdminContainersTests extends GroupTest {
    function AdminContainersTests() {
        $this->GroupTest('Admin Containers Tests');
        $this->addTestFile('admin_db_test.php');
        $this->addTestFile('admin_mdb_test.php');
        $this->addTestFile('admin_mdb2_test.php');
        //$this->addTestFile('admin_gettext_test.php');
        //$this->addTestFile('admin_xml_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new AdminContainersTests();
    $test->run(new HtmlReporter());
}
?>