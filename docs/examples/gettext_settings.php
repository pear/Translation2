<?php
require_once 'settings.php';

$driver = 'gettext';

$params = array(
    'prefetch' => false
);

$dbinfo = array(
    'langs_avail_file'  => 'gettext_langs.ini',
    'domains_path_file' => 'gettext_domains.ini',
    'default_domain'    => 'calendar'
);

?>
