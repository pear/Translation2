<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Translation2_Decorator. Base Decorator class for Translation2
 *
 * Extend this class to provide custom decorators.
 * Some decorators are already bundled with the package.
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
 * Translation2_Decorator base class
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://pear.php.net/package/Translation2
 * @abstract
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
     * @var string
     * @access protected
     */
    var $currentPageID;

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
                $this->setOption($option, $value);
            }
        }
    }

    // }}}
    // {{{ setOption()

    /**
     * set Decorator option
     *
     * @param string option name
     * @param mixed  option value
     */
    function setOption($option, $value=null)
    {
        if (isset($this->$option)) {
            $this->$option = $value;
        } elseif (is_a($this->translation2, 'Translation2_Decorator')) {
            $this->translation2->setOption($option, $value);
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
        if (func_num_args() > 1) {
            $obj =& func_get_arg(1);
            return $this->translation2->getDecorator($decorator, $obj);
        } else {
            return $this->translation2->getDecorator($decorator, $this);
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
     * @param array $params
     */
    function setParams($params=null)
    {
        $this->translation2->setParams($params);
    }

    // }}}
    // {{{ getRaw()

    /**
     * Get translated string
     *
     * No filter is applied.
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
        return $this->translation2->getRaw($stringID, $pageID, $langID, $defaultText);
    }

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * All the filters are applied.
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the string is empty
     *               NB: This parameter is only used in the DefaultText decorator
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
     * Fetch the strings from the container, without any replacing
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
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null)
    {
        $this->translation2->getPage($pageID, $langID);
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
    // {{{ getStringID()

    /**
     * Get the stringID for the given string. This method is the reverse of get().
     *
     * @param string $string This is NOT the stringID, this is a real string.
     *               The method will search for its matching stringID, and then
     *               it will return the associate string in the selected language.
     * @param string $pageID
     * @return string
     */
    function getStringID($string, $pageID=TRANSLATION2_DEFAULT_PAGEID)
    {
        return $this->translation2->getStringID($string, $pageID);
    }

    // }}}
}
?>