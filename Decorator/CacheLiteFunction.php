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
 * and Cache_Lite_Function class
 */
require_once 'Translation2/Decorator.php';
require_once 'Cache/Lite/Function.php';

/**
 * Decorator to cache fetched data using Cache_Lite_Function class
 * @package Translation2
 * @access public
 */
class Translation2_Decorator_CacheLiteFunction extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * Cache_Lite_Function object
     * @var object
     * @access protected
     */
    var $cacheLiteFunction = null;

    /**
     * @var int (default 1)
     * @access private
     */
    var $tempVarNameGenerator = 1;

    /**
     * @var string
     * @access private
     */
    var $tempVarName = null;

    /**
     * Cache lifetime (in seconds)
     * @var int $lifeTime
     * @access private
     */
    var $lifeTime = 3600;

    /**
     * Directory where to put the cache files
     * (make sure to add a trailing slash)
     * @var string $cacheDir
     * @access private
     */
    var $cacheDir = '/tmp/';

    /**
     * Directory where to put the cache files
     * (make sure to add a trailing slash)
     * @var string $cacheDir
     * @access private
     */
    var $fileLocking = true;

    /**
     * Enable / disable caching
     * (can be very useful to debug cached scripts)
     * @var boolean $caching
     */
    var $caching = true;

    // }}}
    // {{{ _prepare()

    /**
     * Istanciate a new Cache_Lite_Function object
     * and get the name for an unused global variable,
     * needed by Cache_Lite_Function
     * @access private
     */
    function _prepare()
    {
        if (is_null($this->cacheLiteFunction)) {
            $cache_options = array(
                'caching'     => $this->caching,
                'cacheDir'    => $this->cacheDir,
                'lifeTime'    => $this->lifeTime,
                'fileLocking' => $this->fileLocking,
            );
            $this->cacheLiteFunction = new Cache_Lite_Function($cache_options);
        }

        //generate temp variable
        if (is_null($this->tempVarName)) {
            $prefix = 'translation2_temp_';
            $var = $this->tempVarNameGenerator++;
            if ($var > 26) {
                $this->tempVarName = $prefix. chr(64 + $var % 26) . chr(64 + $var / 26);
            } else {
                $this->tempVarName = $prefix. chr(64 + $var);
            }
        }
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
        if (is_null($pageID)) {
            $pageID = $this->translation2->currentPageID;
        }

        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        //echo '<pre>'; print_r(get_object_vars($this)); exit;

        return $this->cacheLiteFunction->call($this->tempVarName.'->get',
            $stringID, $pageID, $langID, $defaultText);
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
        if (is_null($pageID)) {
            $pageID = $this->translation2->currentPageID;
        }
        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        return $this->cacheLiteFunction->call($this->tempVarName.'->getRawPage',
            $pageID, $langID);
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
        if (is_null($pageID)) {
            $pageID = $this->translation2->currentPageID;
        }
        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        return $this->cacheLiteFunction->call($this->tempVarName.'->getPage',
            $pageID, $langID);
    }

    // }}}
    // {{{ translate()

    /**
     * Get translated string
     *
     * @param string $string This is NOT the stringID, this is a real string.
     *               The method will search for its matching stringID, and then
     *               it will return the associate string in the selected language.
     * @param string $langID
     * @return string
     */
    function translate($string, $langID, $pageID=TRANSLATION2_DEFAULT_PAGEID)
    {
        if (is_null($pageID)) {
            $pageID = $this->translation2->currentPageID;
        }
        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        return $this->cacheLiteFunction->call($this->tempVarName.'->translate',
            $string, $langID, $pageID);
    }

    // }}}
}
?>