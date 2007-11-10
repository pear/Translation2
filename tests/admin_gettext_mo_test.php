<?php
// $Id$

require_once 'admin_gettext_test_base.php';

class TestOfAdminContainerGettextMO extends TestOfAdminContainerGettextPO {
    function TestOfAdminContainerGettextMO($name='Test of Admin Container Gettext MO') {
        $this->UnitTestCase($name);
    }
    function setUp() {
        $this->init();
        $driver = 'gettext';
        $options = array(
            'prefetch'          => false,
            'langs_avail_file'  => 'gettext_langs.ini',
            'domains_path_file' => 'gettext_domains.ini',
            'default_domain'    => 'messages',
            'file_type'         => 'mo',
        );
        $this->tr = Translation2_Admin::factory($driver, $options);
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfAdminContainerGettextMO();
    $test->run(new HtmlReporter());
}
?>