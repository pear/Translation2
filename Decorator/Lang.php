<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Decorator_Lang class
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
 * Decorator to provide a fallback language for empty strings.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Decorator_Lang extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * fallback lang
     * @var string
     * @access protected
     */
    var $fallbackLang;

    // }}}
    // {{{ setOption()

    /**
     * set Decorator option (intercept 'fallbackLang' option).
     * I don't know why it's needed, but it doesn't work without.
     *
     * @param string option name
     * @param mixed  option value
     */
    function setOption($option, $value=null)
    {
        if ($option == 'fallbackLang') {
            $this->fallbackLang = $value;
        } else {
            parent::setOption($option, $value);
        }
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