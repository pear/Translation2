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
require_once 'Translation2'.DIRECTORY_SEPARATOR.'Decorator.php';

/**
 * Allows redefinition of alternate key for empty pageID
 */
if (!defined('TRANSLATION2_EMPTY_PAGEID_KEY')) {
    define('TRANSLATION2_EMPTY_PAGEID_KEY', 'array_key_4_empty_pageID');
}
/**
 * Allows redefinition of alternate key for null pageID
 */
if (!defined('TRANSLATION2_NULL_PAGEID_KEY')) {
    define('TRANSLATION2_NULL_PAGEID_KEY', 'array_key_4_null_pageID');
}

/**
 * Decorator to cache fetched data in memory
 * @package Translation2
 * @access public
 */
class Translation2_Decorator_CacheMemory extends Translation2_Decorator
{
    // {{{ class vars

    // }}}
    // {{{ _getPageIDKey()

    /**
     * return a valid array key based on pageID value
     *
     * @param mixed $pageID (string or null)
     * @return string
     */
    function _getPageIDKey($pageID)
    {
        if (is_null($pageID)) {
            return TRANSLATION2_NULL_PAGEID_KEY;
        }
        if (empty($pageID)) {
            return TRANSLATION2_EMPTY_PAGEID_KEY;
        }
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            return $this->translation2->currentPageID;
        }
        return $pageID;
    }

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * First check if the string is cached, if not => fetch the page
     * from the container and cache it for later use.
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
        $pageID_key = $this->_getPageIDKey($pageID);
        $langID_key = empty($langID) ? $this->translation2->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->data)) {
            $this->data[$langID_key] = array();
        }

        if ($this->prefetch) {
            $this->getPage($pageID, $langID);
        }
        //$str = $defaultText;
        if (array_key_exists($pageID_key, $this->data[$langID_key])) {
            $str = (isset($this->data[$langID_key][$pageID_key][$stringID]) ?
                    $this->data[$langID_key][$pageID_key][$stringID] : '');
        } else {
            $str = $this->translation2->get($stringID, $pageID, $langID);
        }
        return $this->_replaceParams($str);
    }

    // }}}
    // {{{ getRawPage()

    /**
     * Get the array of strings in a page
     *
     * First check if the strings are cached, if not => fetch the page
     * from the container and cache it for later use.
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getRawPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null)
    {
        $pageID_key = $this->_getPageIDKey($pageID);
        $langID_key = empty($langID) ? $this->translation2->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->rawData)) {
            $this->rawData[$langID_key] = array();
        }
        if (!array_key_exists($pageID_key, $this->rawData[$langID_key])) {
            $this->rawData[$langID_key][$pageID_key] =
                $this->translation2->getRawPage($pageID, $langID);
        }
        return $this->rawData[$langID_key][$pageID_key];
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
        $pageID_key = $this->_getPageIDKey($pageID);
        $langID_key = empty($langID) ? $this->translation2->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->data)) {
            $this->data[$langID_key] = array();
        }
        if (!array_key_exists($pageID_key, $this->data[$langID_key])) {
            $this->data[$langID_key][$pageID_key] =
                $this->translation2->getPage($pageID, $langID);
        }
        $this->data[$langID_key][$pageID_key] = $this->_replaceParams($this->data[$langID_key][$pageID_key]);
        return $this->data[$langID_key][$pageID_key];
    }

    // }}}
}
?>