<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Lorenzo Alberton <l dot alberton at quipo dot it>           |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'Translation2/Admin/Decorator.php';

/**
 * Automatically add requested strings
 *
 * This Decorator will add strings to a language when a request for them to be
 * translated happens. The 'autoaddlang' option must be set to the language the
 * strings will be added as.
 *
 * Example:
 *
 * $tr =& Translation2_Admin::factory(...);
 * $tr =& $tr->getAdminDecorator('Autoadd');
 * $tr->setOption('autoaddlang', 'en');
 * ...
 * $tr->translate('Entirely new string', 'de');
 *
 * 'Entirely new string' will be added to the English language table.
 *
 * @package Translation2
 * @version @version@
 * @author Ian Eure <ieure>
 * @since 2.0.0beta3
 */
class Translation2_Admin_Decorator_Autoadd extends Translation2_Admin_Decorator {

    /**
     * Language to add strings in
     *
     * @var  string
     */
    var $autoaddlang = '';

    /**
     * Translate a string
     *
     * @todo  Return $this->translation2->translate(...) instead of the raw
     *        string. Unfortunately, we have to be at the bottom of the
     *        decorator stack (admin methods aren't proxied by regular
     *        decorators), so we don't get the benefit of Lang / DefaultText
     *        decorators, and return an empty string.
     * @see   Translation2::translate()
     */
    function translate($string, $lang = null, $page = TRANSLATION2_DEFAULT_PAGEID)
    {
        $page = ($page == TRANSLATION2_DEFAULT_PAGEID ? $this->translation2->currentPageID : $page);
        $stringID = $this->translation2->storage->getStringID($string, $page);
        if (PEAR::isError($stringID) || empty($stringID) && 
            !empty($this->autoaddlang)) {
            // Add the string.
            $this->translation2->add($string, $page, array(
                $this->autoaddlang => $string
            ));
        }
        return $string;
    }
}

?>