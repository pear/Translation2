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
//

require_once 'Translation2/Decorator.php';

/**
 * Decorator to return the requested string when a translation doesn't exist.
 * @package Translation2
 * @version $Revision$
 * @author Ian Eure
 */
class Translation2_Decorator_Rawfallback extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * raw fallback
     * @var boolean
     * @access protected
     */
    var $rawfallback = false;

    // }}}
    // {{{ setOption()

    /**
     * Set decorator option (intercept 'rawfallback' option).
     *
     * @param string option name
     * @param mixed  option value
     */
    function setOption($option, $value=null)
    {
        if ($option == 'rawfallback') {
            $this->rawfallback = $value;
        } else {
            parent::setOption($option, $value);
        }
    }

    // }}}
    // {{ translate()

    /**
     * Translate a string.
     *
     * If the 'rawfallback' option is set, this will simply return $string back
     * to the caller.
     *
     * @see  Translation2::translate()
     */
    function &translate($string, $langID, $pageID=TRANSLATION2_DEFAULT_PAGEID)
    {
        $str =& parent::translate($string, $langID, $pageID);
        if (empty($str) && $this->rawfallback === true) {
            $str =& $string;
        }
        return $str;
    }

    // }}}
}
?>