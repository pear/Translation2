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
 * require PEAR base class
 */
require_once 'PEAR.php';

/**
 * Allows redefinition of the default pageID.
 * This constant is needed to allow both NULL and EMPTY pageID values
 * and to have them match
 *
 */
if (!defined('TRANSLATION2_DEFAULT_PAGEID')) {
    define('TRANSLATION2_DEFAULT_PAGEID', 'translation2_default_pageID');
}
/**
 * Class Error codes
 */
define('TRANSLATION2_ERROR',                      -1);
define('TRANSLATION2_ERROR_METHOD_NOT_SUPPORTED', -2);
define('TRANSLATION2_ERROR_CANNOT_CONNECT',       -3);
define('TRANSLATION2_ERROR_CANNOT_FIND_FILE',     -4);
define('TRANSLATION2_ERROR_DOMAIN_NOT_SET',       -5);
define('TRANSLATION2_ERROR_INVALID_PATH',         -6);
define('TRANSLATION2_ERROR_CANNOT_CREATE_DIR',    -7);

/**
 * Translation2 class
 * @package Translation2
 */
class Translation2
{
    // {{{ class vars

    /**
     * Storage object
     * @var object
     */
    var $storage = '';

    /**
     * Class options
     * @var array
     */
    var $options = array();

    /**
     * Default lang
     * @var array
     */
    var $lang = array();

    /**
     * Current pageID
     * @var string
     */
    var $currentPageID = null;

    /**
     * Array parameters
     * @var array
     */
    var $params = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * @param string $storageDriver Type of the storage driver
     * @param mixed  $options Additional options for the storage driver
     *                        (example: if you are using DB as the storage
     *                        driver, you have to pass the dsn string here)
     * @param array $params Array of parameters for the adapter class
     *                      (i.e. you can set here the mappings between your
     *                      table/field names and the ones used by this class)
     */
    function Translation2($storageDriver, $options='', $params=array())
    {
        if (is_object($storageDriver)) {
            $this->storage =& $storageDriver;
        } else {
            $this->storage = $this->_factory($storageDriver, $options);
        }
        $this->_setDefaultOptions();
        $this->_parseOptions($params);
        $this->storage->_parseOptions($params);
    }

    // }}}
    // {{{ _factory()

    /**
     * Return a storage driver based on $driver and $options
     *
     * @access private
     * @static
     * @param  string $driver  Type of storage class to return
     * @param  string $options Optional parameters for the storage class
     * @return object Object   Storage object
     */
    function _factory($driver, $options='')
    {
        $storage_path = 'Translation2/Container/'.strtolower($driver).'.php';
        $storage_class = 'Translation2_Container_'.strtolower($driver);
        require_once $storage_path;
        return new $storage_class($options);
    }

    // }}}
    // {{{ _setDefaultOptions()

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaultOptions()
    {
        $this->options['ParameterPrefix']   = '&&';
        $this->options['ParameterPostfix']  = '&&';
        $this->options['ParameterAutoFree'] = true;
        $this->options['prefetch']          = true;
    }

    // }}}
    // {{{ _parseOptions()

    /**
     * Parse options passed to the base class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        foreach ($array as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }

    // }}}
    // {{{ getDecorator()

    /**
     * Return an instance of a decorator
     *
     * @access public
     * @param  string $decorator  Name of the decorator
     * @param  object [optional]
     * @return object Decorator object reference
     */
    function & getDecorator($decorator)
    {
        $decorator_path = 'Translation2/Decorator/'.$decorator.'.php';
        $decorator_class = 'Translation2_Decorator_'.$decorator;
        require_once $decorator_path;
        if (func_num_args() > 1) {
            $obj =& func_get_arg(1);
            return new $decorator_class($obj);
        } else {
            return new $decorator_class($this);
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
        $this->lang = $this->storage->setLang($langID);
    }

    // }}}
    // {{{ setPageID($pageID)

    /**
     * Set default page
     * @param string $langID
     */
    function setPageID($pageID=null)
    {
        $this->currentPageID = $pageID;
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
        if (is_null($langID) || ($langID == $this->lang['id'])) {
            $lang = $this->lang;
        } else {
            $lang = $this->storage->getLangData($langID);
        }

        if ($format == 'array') {
            return $lang;
        } elseif (isset($lang[$format])) {
            return $lang[$format];
        } else {
            return $lang['name'];
        }
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
        return $this->storage->getLangs($format);
    }

    // }}}
    // {{{ setParams()

    /**
     * Set parameters for next string
     * @param array $params
     */
    function setParams($params=null)
    {
        if (empty($params)) {
            $this->params = array();
        } elseif (is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = array($params);
        }
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
        if (empty($strings) || is_object($strings) || !count($this->params)) {
            return $strings;
        }
        if (is_array($strings)) {
            foreach ($strings as $key => $string) {
                $strings[$key] = $this->_replaceParams($string);
            }
        } else {
            if (strpos($strings, $this->options['ParameterPrefix']) !== false) {
                foreach ($this->params as $name => $value) {
        		    $strings = str_replace($this->options['ParameterPrefix']
        			            	       . $name . $this->options['ParameterPostfix'],
        			                       $value,
        			                       $strings);
                }
                if ($this->options['ParameterAutoFree']) {
                    $this->params = array();
                }
            }
        }
        return $strings;
    }

    // }}}
    // {{{ replaceEmptyStringsWithKeys()

    /**
     * Replace empty strings with their stringID
     * @param mixed $params
     * @static
     * @access public
     */
    function replaceEmptyStringsWithKeys($strings)
    {
        if (!is_array($strings)) {
            return $strings;
        }
        foreach ($strings as $key => $string) {
            if (empty($string)) {
                $strings[$key] = $key;
            }
        }
        return $strings;
    }

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
        $pageID = ($pageID == TRANSLATION2_DEFAULT_PAGEID ? $this->currentPageID : $pageID);
        $str = $this->storage->getOne($stringID, $pageID, $langID);
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
        $pageID = ($pageID == TRANSLATION2_DEFAULT_PAGEID ? $this->currentPageID : $pageID);
        return $this->storage->getPage($pageID, $langID);
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
        $pageData = $this->getRawPage($pageID, $langID);
        return $this->_replaceParams($pageData);
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
        $pageID = ($pageID == TRANSLATION2_DEFAULT_PAGEID ? $this->currentPageID : $pageID);
        $stringID = $this->storage->getStringID($string, $pageID);
        if (PEAR::isError($stringID) || empty($stringID)) {
            return $this->lang['error_text'];
        }
        return $this->get($stringID, $pageID, $langID);
    }

    // }}}
}
?>