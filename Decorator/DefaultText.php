<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Decorator_DefaultText class
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
 * @author     Rolf 'Red' Ochsenbein <red at raven dot ch>
 * @copyright  2004-2005 Lorenzo Alberton, Rolf 'Red' Ochsenbein
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * Load Translation2 decorator base class
 */
require_once 'Translation2/Decorator.php';

/**
 * Decorator to provide a fallback text for empty strings.
 *
 * If the string is empty, return the <parameter>defaultText</parameter> parameter.
 * If the <parameter>defaultText</parameter> parameter is empty too, then return
 * &quot;$emptyPostfix.$outputString.$emptyPrefix&quot;, the three variables
 * being class properties you can set to a custom string.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Rolf 'Red' Ochsenbein <red at raven dot ch>
 * @copyright  2004-2005 Lorenzo Alberton, Rolf 'Red' Ochsenbein
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Decorator_DefaultText extends Translation2_Decorator
{
    // {{{ class vars

    /**
     * String appended to the returned string when the string is empty
     * and it's replaced by its $stringID. It can be used to mark unreplaced
     * strings.
     * @var string
     * @access protected
     */
    var $emptyPostfix = '';

    /**
     * String prepended to the returned string when the string is empty
     * and it's replaced by its $stringID. It can be used to mark unreplaced
     * strings.
     * @var string
     * @access protected
     */
    var $emptyPrefix = '';

    /**
     * String to output when there was no translation
     * %stringID% will be replaced with the stringID
     * %stringID_url% will replaced with a urlencoded stringID
     * %url% will be replaced with the targeted url
     * @var string
     * @access protected
     */
    //var $outputString = '%stringID%<a href="%url%">(T)</a>';
    var $outputString = '%stringID%';

    /**
     * Targeted URL of strings without translations
     * @var string
     * @access protected
     */
    var $url = '#';

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * If the string is empty, return the $defaultText if not empty,
     * the $stringID otherwise.
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the string is empty
     * @return string
     */
    function get($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText='')
    {
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $str = $this->translation2->get($stringID, $pageID, $langID);

        if (!empty($str)) {
            return $str;
        }
        if (!empty($defaultText)) {
            return $defaultText;
        }

        $search  = array(
            '%stringID%',
            '%stringID_url%',
            '%pageID_url%',
            '%url%'
        );
        $replace = array(
            $stringID,
            urlencode($stringID),
            urlencode($pageID),
            $this->url
        );
        return $this->emptyPrefix.str_replace($search, $replace, $this->outputString).$this->emptyPostfix;
        //$str = (empty($defaultText) ? $this->emptyPrefix.$stringID.$this->emptyPostfix : $defaultText);
    }

    // }}}
    // {{{ getPage()

    /**
     * Replace empty strings with their $stringID
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null)
    {
        $data = $this->translation2->getPage($pageID, $langID);
        return $this->replaceEmptyStringsWithKeys($data);
    }

    // }}}
    // {{{ getStringID

    /**
     * Get the stringID for the given string. This method is the reverse of get().
     * If the requested string is unknown to the system,
     * the requested string will be returned.
     *
     * @param string $string This is NOT the stringID, this is a real string.
     *               The method will search for its matching stringID, and then
     *               it will return the associate string in the selected language.
     * @param string $pageID
     * @return string
     */
    function &getStringID($string, $pageID=TRANSLATION2_DEFAULT_PAGEID)
    {
        if ($pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->translation2->currentPageID;
        }
        $stringID = $this->storage->getStringID($string, $pageID);
        if (empty($stringID)) {
            $stringID = $string;
        }
        return $stringID;
    }
}
?>