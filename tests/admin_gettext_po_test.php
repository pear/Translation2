<?php
// $Id$

require_once 'admin_gettext_test_base.php';

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfAdminContainerGettextPO();
    $test->run(new HtmlReporter());
}
?>