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

    /**
     * Frequency of cache cleaning. 
     * Higher values mean lower cleaning probability.
     * Set 0 to disable. Set 1 to clean at every request.
     * @var boolean $caching
     */
    var $cleaningFrequency = 0;

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

        $this->_cleanCache();
        //generate temp variable
        if (is_null($this->tempVarName)) {
            $this->tempVarName = 'translation2_temp_' . $this->tempVarNameGenerator++;
        }
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
    function getRaw($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText='')
    {
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $langID = empty($langID) ? $this->translation2->lang['id'] : $langID;

        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        return $this->cacheLiteFunction->call($this->tempVarName.'->getRaw',
            $stringID, $pageID, $langID, $defaultText);
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
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $langID = empty($langID) ? $this->translation2->lang['id'] : $langID;

        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2->storage;

        $string = $this->cacheLiteFunction->call($this->tempVarName.'->getOne',
            $stringID, $pageID, $langID);
        if (empty($string)) {
            return $defaultText;
        }
        return $this->translation2->_replaceParams($string);
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
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $langID = empty($langID) ? $this->translation2->lang['id'] : $langID;

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
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $langID = empty($langID) ? $this->translation2->lang['id'] : $langID;

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
    function getStringID($string, $pageID=TRANSLATION2_DEFAULT_PAGEID)
    {
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $this->_prepare();
        global ${$this->tempVarName}; // WITHOUT THIS, IT DOESN'T WORK
        ${$this->tempVarName} = $this->translation2;

        return $this->cacheLiteFunction->call($this->tempVarName.'->getStringID',
            $string, $pageID);
    }

    // }}}
    // {{{ _cleanCache()

    /**
     * Statistically purge the cache
     */
    function _cleanCache()
    {
        if ($this->cleaningFrequency > 0) {
            if (mt_rand(1, $this->cleaningFrequency) == 1) {
            	$this->cacheLiteFunction->clean();
            }
        }
    }

    // }}}
}
?>