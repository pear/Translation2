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
// | Author: Lorenzo Alberton <l dot alberton at quipo dot it>            |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * Class used for namespace emulation.
 * Only static methods used.
 *
 * @package  Translation2
 * @version  $Revision$
 */
class Translation2_Utils
{
    // {{{ po_parser()

    /**
     * Parse .po files
     *
     * NB: the parsing algorithm is VERY naïve!!!
     *
     * @param string domain path
     * @param string domain name
     * @return array with 'stringID' => 'string' pairs
     */
    function po_parser($path, $domain)
    {
        $filename = $path.DIRECTORY_SEPARATOR.$domain.'.po';
        $lines = @file($filename);
        if ($lines === false) {
            //raise error
            return array();
        }

        $lines = array_filter($lines, array('Translation2_Utils', 'purgeExtraLines'));
        $strings = array();
        foreach (array_keys($lines) as $i) {
            if (substr($lines[$i], 0, 6) == 'msgstr') {
                continue;
            }
            //beware: this one supposes 'msgid' and 'msgstr' on two subsequent lines
            $stringID = substr(trim($lines[$i]), 7, -1);
            $string = substr(trim($lines[$i+1]), 8, -1);
            if (empty($stringID)) {
                continue;
            }
            $strings[$stringID] = $string;
        }
        return $strings;
    }

    // }}}
    // {{{ purgeExtraLines()

    /**
     * Callback function.
     *
     * @param string
     * @return boolean true if string starts with 'msgid' or 'msgstr'
     */
    function purgeExtraLines($str)
    {
        if ((substr($str, 0, 5) == 'msgid') || (substr($str, 0, 6) == 'msgstr')) {
            return true;
        }
        return false;
    }

    // }}}
}
?>