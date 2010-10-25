<?php
// $Id$

require_once 'simple_include.php';
require_once 'translation2_include.php';

define('TEST_RUNNING', true);

require_once './containers_tests.php';
require_once './admin_containers_tests.php';


class AllTests extends GroupTest {
    function AllTests() {
        $this->GroupTest('All PEAR::Translation2 Tests');
        $this->AddTestCase(new ContainersTests());
        $this->AddTestCase(new AdminContainersTests());
    }
}

$test = &new AllTests();
$test->run(new HtmlReporter());
?>