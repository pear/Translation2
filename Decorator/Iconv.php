<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Decorator_Iconv class
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
 * @author     Sergey Korotkov <sergey@pushok.com>
 * @copyright  2004-2005 Lorenzo Alberton, Sergey Korotkov
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * Load Translation2 decorator base class
 */
require_once 'Translation2/Decorator.php';

/**
 * Translation2 Iconv Decorator
 *
 * Decorator to change the encoding of the stored translation to the
 * one given in the 'encoding' option.
 *
 * <code>
 * $tr->setOptions(array('encoding' => 'UTF-8'));
 * </code>
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Sergey Korotkov <sergey@pushok.com>
 * @copyright  2004-2005 Lorenzo Alberton, Sergey Korotkov
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 * @see http://www.php.net/htmlentities for a list of available encodings.
 */
class Translation2_Decorator_Iconv extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * @var string
     * @access private
     */
    var $encoding = 'ISO-8859-1';
    
    /**
     * @var array
     * @access private
     */
    var $lang_encodings;

    // }}}
    // {{{ _getEncoding()

    /**
     * Get the encoding for the given langID
     *
     * @param string $langID
     * @return string encoding
     * @access private
     */
    function _getEncoding($langID = null)
    {
        if (!is_array($this->lang_encodings)) {
            $this->lang_encodings = array();
            foreach ($this->translation2->getLangs('encodings') as $langID => $encoding) {
                $this->lang_encodings[$langID] = $encoding;
            }
        }
        if (!is_null($langID) && isset($this->lang_encodings[$langID])) {
            return $this->lang_encodings[$langID];
        }
        return $this->lang['encoding'];
    }

    // }}}
    // {{{ get()
    
    /**
     * Get the translated string, in the new encoding
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the string is empty
     * @return string
     */
    function get($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText=null)
    {
        $str = $this->translation2->get($stringID, $pageID, $langID, $defaultText);
        
        if (PEAR::isError($str) || empty($str)) {
            return $str;
        }

        return iconv($this->_getEncoding($langID), $this->encoding, $str);
    }

    // }}}
    // {{{ getPage()

    /**
     * Same as getRawPage, but apply transformations when needed
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null)
    {
        $data = $this->translation2->getPage($pageID, $langID);
        
        $input_encoding = $this->_getEncoding($langID);
        
        foreach (array_keys($data) as $k) {
            if (!empty($data[$k])) {
                $data[$k] = iconv($input_encoding, $this->encoding, $data[$k]);
            }
        }
        return $data;
    }

    // }}}
}
?>