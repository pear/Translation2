Package: PEAR::Translation2
http://pear.php.net/Translation2
--------------------------------

This is an *experimental* gettext driver for Translation2.

Gettext is designed to offer the best performance when
used on its own, without wrappers like this one.

This driver has an internal .po parser to offer
the getPage() functionality. To make it work, 
.po files must be placed in the same dir as
the matching .mo files.
This can be useful to get all the strings from a domain
and to pass them to a template engine, for instance.

Obviously, this also lead to worse gettext performance.
If you want to use gettext at the max speed,
don't use this class ;), or at least turn 'prefetch' off
AND don't use getPage() method.

End of the necessary preface :)


=============================
Usage example
=============================
<?php

$params = array(
    'prefetch' => false
);
$gettext_options = array(
    'langs_avail_file' => '/path/to/langs.ini',
    'domains_path_file' => '/path/to/domains.ini',
    'default_domain' => 'messages'
);

$tr = new Translation2('gettext', null, $params);
$tr->setLang('en');

echo $tr->get('mystring');

$tr->setPage('otherDomain');
echo $tr->get('aStringFromOtherDomain');

print_r($tr->getPage('thirdDomain'));

?>


=============================
langs.ini file format example
=============================
; lang code can be in "lang_DIALECT" or in "lang" format

[en_UK]
id = en_UK
name = English
meta = iso-8859-1
error_text = not available in English

[it]
id = it
name = italiano
meta = iso-8859-1
error_text = non disponibile in italiano


===============================
domains.ini file format example
===============================
; path the "locale" dir of each domain
messages = /usr/data/locale
errors = /usr/data/locale
myApp =  /usr/data/locale
myOtherApp = /usr/newData/locale

