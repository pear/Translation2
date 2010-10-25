<?php
// $Id$

require_once 'db_test_base.php';

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfContainerDB();
    $test->run(new HtmlReporter());
}
?>