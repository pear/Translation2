<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Decorator_SpecialChars class
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
 * Decorator to replace special chars with the matching html entities.
 *
 * You can set the charset to use (the default being 'ISO-8859-1'):
 * <code>
 * $tr->setOptions(array('charset' => 'UTF-8'));
 * </code>
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 * @see        http://www.php.net/htmlentities for a list of available charsets.
 */
class Translation2_Decorator_SpecialChars extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * @var string
     * @access protected
     */
    var $charset = 'ISO-8859-1';

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * replace special chars with the matching html entities
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
        if (!empty($str)) {
            $str = htmlentities($str, ENT_QUOTES, $this->charset);
        }
        return $str;
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
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $data[$key] = htmlentities($val, ENT_QUOTES, $this->charset);
            }
        }
        return $data;
    }

    // }}}
}
?>