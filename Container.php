<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Container base class
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
 * Base class for Translation2 drivers/containers
 *
 * Extend this class to provide custom containers.
 * Some containers are already bundled with the package.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Container
{
    // {{{ Class vars

    /**
     * Additional options for the storage container
     * @var array
     */
    var $options = array();

    /**
     * @var array
     * @access private
     */
    var $currentLang = array();

    /**
     * @var array
     * @access private
     */
    var $langs = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     * Has to be overwritten by each storage class
     * @access public
     */
    function Translation2_Container()
    {
    }

    // }}}
    // {{{ _parseOptions()

    /**
     * Parse options passed to the container class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }

    // }}}
    // {{{ _getLangID()

    /**
     * Get a valid langID or raise an error when
     *
     * @access private
     * @param  string $langID
     */
    function _getLangID($langID)
    {
        if (!empty($langID)) {
            return $langID;
        }
        if (!empty($this->currentLang['id'])) {
            return $this->currentLang['id'];
        }
        $msg = 'No valid language set. Use Translation2::setLang().';
        return $this->raiseError($msg, TRANSLATION2_ERROR_UNKNOWN_LANG);
    }

    // }}}
    // {{{ setLang()

    /**
     * Sets the current lang
     *
     * @param  string $langID
     */
    function setLang($langID)
    {
        $this->getLangs(); //load available languages, if not loaded yet (ignore return value)
        if (!array_key_exists($langID, $this->langs)) {
            return $this->raiseError('unknown language: "'.$langID.'"',
                                    TRANSLATION2_ERROR_UNKNOWN_LANG,
                                    PEAR_ERROR_RETURN,
                                    E_USER_WARNING);
        }
        $this->currentLang = $this->langs[$langID];
        return $this->langs[$langID];
    }

    // }}}
    // {{{ getLang()

    /**
     * Gets the current lang
     * @param string $format
     * @return mixed array with current lang data or null if not set yet
     */
    function getLang($format='id')
    {
        return isset($this->currentLang['id']) ? $this->currentLang : null;
    }

    // }}}
    // {{{ getLangData()

    /**
     * Gets the array data for the lang
     * @param  string $langID
     * @param string $format
     * @return mixed array with lang data or null if not available
     */
    function getLangData($langID, $format='id')
    {
        $langs = $this->getLangs('array');
        return isset($langs[$langID]) ? $langs[$langID] : null;
    }

    // }}}
    // {{{ getLangs()

    /**
     * Gets the available languages
     * @param string $format ['array' | 'ids' | 'names' | 'encodings']
     */
    function getLangs($format='array')
    {
        //if not cached yet, fetch langs data from the container
        if (empty($this->langs) || !count($this->langs)) {
            $this->fetchLangs(); //container-specific method
        }

        $tmp = array();
        switch ($format) {
            case 'array':
                foreach ($this->langs as $aLang) {
                    $tmp[$aLang['id']] = $aLang;
                }
                break;
            case 'ids':
                foreach ($this->langs as $aLang) {
                    $tmp[] = $aLang['id'];
                }
                break;
            case 'encodings':
                foreach ($this->langs as $aLang) {
                    $tmp[] = $aLang['encoding'];
                }
                break;
            case 'names':
            default:
                foreach ($this->langs as $aLang) {
                    $tmp[$aLang['id']] = $aLang['name'];
                }
        }
        return $tmp;
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     * Containers should implement this method.
     */
    function fetchLangs()
    {
        return $this->raiseError('method "fetchLangs" not supported',
                                 TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ getPage()

    /**
     * Returns an array of the strings in the selected page
     * Containers should implement this method.
     * @param string $pageID
     * @return array
     */
    function getPage($pageID, $langID)
    {
        return $this->raiseError('method "getPage" not supported',
                                 TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ getOne()

    /**
     * Get a single item from the container, without caching the whole page
     * Containers should implement this method.
     */
    function getOne($stringID, $pageID=null, $langID=null)
    {
        return $this->raiseError('method "getOne" not supported',
                                 TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ getStringID()

    /**
     * Get the stringID for the given string
     * @param string $stringID
     * @param string $pageID
     * @return string
     */
    function getStringID($string, $pageID)
    {
        return $this->raiseError('method "getStringID" not supported',
                                 TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ raiseError()

    /**
     * Trigger a PEAR error
     *
     * @param string $msg error message
     * @param int $code error code
     * @access public
     */
    function raiseError($msg, $code, $mode=PEAR_ERROR_TRIGGER, $option=E_USER_WARNING)
    {
        //PEAR::raiseError($msg, $code, $this->_pearErrorMode);
        if ($mode == PEAR_ERROR_RETURN) {
            return PEAR::raiseError($msg, $code, $mode, $option);
        } else {
            PEAR::raiseError($msg, $code, $mode, $option);
        }
    }

    // }}}
}
?>