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
 * Decorates a Translation2 class.
 * Create a subclass of this class for your own "decoration".
 * @abstract
 * @package Translation2
 */
class Translation2_Decorator
{
    // {{{ class vars

    /**
     * Translation2 object being decorated
     * @var object
     * @access protected
     */
    var $translation2;

    /**
     * set prefetch on/off
     * @var boolean
     * @access protected
     */
    var $prefetch = true;

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
     * Used for debug only.
     * @var object
     * @access protected
     */
    var $storage;

    /**
     * @var array
     * @access protected
     */
    var $lang;

    /**
     * decorated lang
     * @var string
     * @access protected
     */
    var $currentLang;

    /**
     * @var string
     * @access protected
     */
    var $currentPageID;

    /**
     * @var string
     * @access protected
     */
    var $charset = 'ISO-8859-1';

    /**
     * Cache_Lite_Function object
     * @var object
     * @access protected
     */
    //var $cacheLiteFunction;

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * Constructs the Translation2_Decorator
     * @param object Translation2 object to decorate
     */
    function Translation2_Decorator(& $translation2)
    {
        $this->translation2 = & $translation2;
        //used for debug only
        $this->storage = & $translation2->storage;
        $this->currentPageID = & $translation2->currentPageID;
        $this->lang = & $translation2->lang;
    }

    // }}}
    // {{{ setOptions()

    /**
     * set Decorator options
     *
     * @param array options
     */
    function setOptions($options=array())
    {
        if (is_array($options)) {
            foreach ($options as $option => $value) {
                if (isset($this->$option)) {
                    $this->$option = $value;
                }
            }
        }
    }

    // }}}
    // {{{ setLang()

    /**
     * Set default lang
     * @param string $langID
     */
    function setLang($langID)
    {
        $this->translation2->setLang($langID);
    }

    // }}}
    // {{{ setPageID($pageID)

    /**
     * Set default page
     * @param string $langID
     */
    function setPageID($pageID=null)
    {
        $this->translation2->setPageID($pageID);
    }

    // }}}
    // {{{ getLang()

    /**
     * get lang info
     * @param string $langID
     * @param string $format ['name', 'meta', 'error_text', 'array']
     * @return mixed [string | array], depending on $format
     */
    function getLang($langID=null, $format='name')
    {
        return $this->translation2->getLang($langID, $format);
    }

    // }}}
    // {{{ getLangs()

    /**
     * get langs
     * @param string $format ['ids', 'names', 'array']
     * @return array
     */
    function getLangs($format='name')
    {
        return $this->translation2->getLangs($format);
    }

    // }}}
    // {{{ setParams()

    /**
     * Set parameters for next string
     * @param params $params
     */
    function setParams($params=null)
    {
        $this->translation2->setParams($params);
    }

    // }}}
    // {{{ setDecoratedLang()

    /**
     * Set default lang
     * @param string $langID
     */
    function setDecoratedLang($langID)
    {
        if (isset($this->translation2->currentLang)) {
            $this->translation2->currentLang = $langID;
        }
    }

    // }}}
    // {{{

    /**
     * Pass a Cache_Lite_Function object to the decorator
     * @param object Cache_Lite_Function instance
     */
    /*
    function setCacheLite(& $cacheLiteFunction)
    {
        $this->cacheLiteFunction = & $cacheLiteFunction;
    }
    */

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * First check if the string is cached, if not => fetch the page
     * from the container and cache it for later use.
     * If the string is empty, check the fallback language; if
     * the latter is empty too, then return the $defaultText.
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
        return $this->translation2->get($stringID, $pageID, $langID, $defaultText);
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
        return $this->translation2->getRawPage($pageID, $langID);
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
        $this->translation2->getPage($pageID, $langID, $defaultText);
    }

    // }}}
    // {{{ _replaceParams()

    /**
     * Replace parameters in strings
     * @param mixed $params
     * @access protected
     */
    function _replaceParams($strings)
    {
        return $this->translation2->_replaceParams($strings);
    }

    // }}}
    // {{{ replaceEmptyStringsWithKeys()

    /**
     * Replace empty strings with their stringID
     * @param mixed $params
     * @access public
     */
    function replaceEmptyStringsWithKeys($strings)
    {
        return $this->translation2->replaceEmptyStringsWithKeys($strings);
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
        return $this->translation2->translate($string, $langID, $pageID);
    }

    // }}}
}
?>