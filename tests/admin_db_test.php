<?php
// $Id$

require_once 'admin_db_test_base.php';

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfAdminContainerDB();
    $test->run(new HtmlReporter());
}
?>