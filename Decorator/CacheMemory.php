<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Translation2 decorator to cache fetched data in memory.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * Load Translation2 decorator base class
 */
require_once 'Translation2/Decorator.php';

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
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Decorator_CacheMemory extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * Translated strings array
     * Used for cache purposes.
     * No parameter substitution or fallback langs here.
     * @var array
     * @access protected
     */
    var $rawData = array();

    /**
     * Translated strings array
     * Used for cache purposes.
     * @var array
     * @access protected
     */
    var $data = array();

    /**
     * set prefetch on/off
     * @var boolean
     * @access protected
     */
    var $prefetch = true;

    // }}}
    // {{{ _getPageIDKey()

    /**
     * return a valid array key based on pageID value
     *
     * @param mixed $pageID (string or null)
     * @return string
     * @access private
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
    // {{{ getRaw()

    /**
     * Get translated string (as-is)
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
    function getRaw($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText=null)
    {
        $pageID_key = $this->_getPageIDKey($pageID);
        $langID_key = empty($langID) ? $this->translation2->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->data)) {
            $this->data[$langID_key] = array();
        }

        if ($this->prefetch) {
            $this->getRawPage($pageID, $langID);
        }
        if (array_key_exists($pageID_key, $this->data[$langID_key])) {
            $str = (isset($this->data[$langID_key][$pageID_key][$stringID]) ?
                    $this->data[$langID_key][$pageID_key][$stringID] : ''); //empty string or null value?
        } else {
            $str = $this->translation2->getRaw($stringID, $pageID, $langID, $defaultText);
        }
        return $str;
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
    function get($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText=null)
    {
        $pageID_key = $this->_getPageIDKey($pageID);
        $langID_key = empty($langID) ? $this->translation2->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->data)) {
            $this->data[$langID_key] = array();
        }

        if ($this->prefetch) {
            $this->getRawPage($pageID, $langID);
        }
        if (array_key_exists($pageID_key, $this->data[$langID_key])) {
            $str = (isset($this->data[$langID_key][$pageID_key][$stringID]) ?
                    $this->data[$langID_key][$pageID_key][$stringID] : ''); //empty string or null value?
        } else {
            $str = $this->translation2->get($stringID, $pageID, $langID, $defaultText);
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
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText=null)
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