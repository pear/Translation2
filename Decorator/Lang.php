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
/**
 * @package Translation2
 * @version $Id$
 */
/**
 * Load Translation2 decorator base class
 */
require_once 'Translation2/Decorator.php';

/**
 * Decorator to provide a fallback language for empty strings.
 * @package Translation2
 */
class Translation2_Decorator_Lang extends Translation2_Decorator
{
    // {{{ class vars

    // }}}
    // {{{ setFallbackLang()

    /**
     * Set the fallback lang. When the requested string
     * is not available in the primary lang, this decorator
     * will try with another lang before returning an empty string.
     *
     * @param string $langID
     */
    function setFallbackLang($langID)
    {
        $this->fallbackLang = $langID;
    }

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * If the string is empty, check the fallback language
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the strings in both
     *                            the default and the fallback lang are empty
     * @return string
     */
    function get($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText='')
    {
        $str = $this->translation2->get($stringID, $pageID, $langID, $defaultText);
        if (empty($str)) {
            $str = $this->translation2->get($stringID, $pageID, $this->fallbackLang);
        }
        return $str;
    }

    // }}}
    // {{{ getPage()

    /**
     * Same as getRawPage, but resort to fallback language and
     * replace parameters when needed
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText='')
    {
        $data1 = $this->translation2->getPage($pageID, $langID);
        $data2 = $this->translation2->getPage($pageID, $this->fallbackLang);
        foreach ($data1 as $key => $val) {
            if (empty($val)) {
                $data1[$key] = $data2[$key];
            }
        }
        return $data1;
    }

    // }}}
}
?>