<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Translation2 gettext admin container
 *
 * This storage driver requires the gettext extension
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
 * @author     Michael Wallner <mike at php dot net>
 * @copyright  2004-2005 Lorenzo Alberton, Michael Wallner
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * require Translation2_Container class
 */
require_once 'Translation2/Container.php';

/**
 * require I18Nv2 for locale handling
 */
require_once 'I18Nv2.php';

/**
 * Storage driver for fetching data with gettext
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Michael Wallner <mike at php dot net>
 * @copyright  2004-2005 Lorenzo Alberton, Michael Wallner
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 * @see        /docs/gettext_readme.txt for an usage example
 */
class Translation2_Container_gettext extends Translation2_Container
{
    // {{{ class vars

    /**
     * domain bindings
     * @var array
     * @access private
     */
    var $_domains = array();

    /**
     * @var array
     * @access private
     */
    var $cachedDomains = array();

    /**
     * @var boolean
     * @access private
     */
    var $_native = false;

    // }}}
    // {{{ init

    /**
     * Initialize the container 
     *
     * @param  array  gettext parameters
     * @return boolean|PEAR_Error object if domains INI file doesn't exist
     */
    function init($options)
    {
        $this->_setDefaultOptions();
        $this->_parseOptions($options);
        $this->_native = (
            function_exists('gettext') &&
            ($this->options['file_type'] != 'po')
        );
        
        $this->_domains = @parse_ini_file($this->options['domains_path_file']);
        
        if (!$this->_domains) {
            return $this->raiseError(sprintf(
                    'Cannot find domains INI file "%s" [%s on line %d]',
                    $this->options['domains_path_file'], __FILE__, __LINE__
                ),
                TRANSLATION2_ERROR_CANNOT_FIND_FILE
            );
        }

        foreach ((array) $this->_domains as $domain => $path) {
            bindtextdomain($domain, $path);
        }
        
        $this->setLang($this->options['default_lang']);
        
        return true;
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
        $this->options['langs_avail_file']  = 'langs.ini';
        $this->options['domains_path_file'] = 'domains.ini';
        $this->options['default_domain']    = 'messages';
        $this->options['carriage_return']   = "\n";
        $this->options['file_type']         = 'mo';
        $this->options['default_lang']      = 'en';
        $this->options['default_encoding']  = 'iso-8859-1';
    }

    // }}}
    // {{{ _switchLang()

    /**
     * @param string new langID
     * @return string previous lang
     * @access private
     */
    function _switchLang($langID)
    {
        $langID  = $this->_getLangID($langID);
        $oldLang = $this->currentLang['id'];
        $this->setLang($langID);
        return $oldLang;
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     *
     * @return void
     */
    function fetchLangs()
    {
        $this->langs = @parse_ini_file($this->options['langs_avail_file'], true);
        foreach ((array) $this->langs as $id => $lang) {
            $this->langs[$id]['id'] = $id;
        }
    }

    // }}}
    // {{{ setLang()

    /**
     * Sets the current lang
     *
     * @param  string $langID
     * @return array Lang data
     */
    function setLang($langID)
    {
        if (!PEAR::isError($langData = parent::setLang($langID))) {
            I18Nv2::setLocale($langID);
        }
        return $langData;
    }

    // }}}
    // {{{ getPage()

    /**
     * Get all the strings from a domain (parsing the .mo file)
     *
     * @param string $pageID
     * @return array|PEAR_Error
     */
    function getPage($pageID = null, $langID = null)
    {
        $oldLang = $this->_switchLang($langID);
        $curLang = $this->currentLang['id'];
        
        if (empty($pageID) || $pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->options['default_domain'];
        }
        
        if (isset($this->cachedDomains[$curLang][$pageID])) {
            return $this->cachedDomains[$curLang][$pageID];
        }
        
        if (!isset($this->_domains[$pageID])) {
            return $this->raiseError(sprintf(
                    'The domain "%s" was not specified in the domains INI '.
                    'file "%s" [%s on line %d]', $pageID,
                    $this->options['domains_path_file'], __FILE__, __LINE__
                ),
                TRANSLATION2_ERROR_DOMAIN_NOT_SET
            );
        }
        
        require_once 'File/Gettext.php';
        $gtFile = &File_Gettext::factory($this->options['file_type']);
        
        $path = $this->_domains[$pageID] .'/'. $curLang .'/LC_MESSAGES/';
        $file = $path . $pageID .'.'. $this->options['file_type'];

        if (PEAR::isError($e = $gtFile->load($file))) {
            if (is_file($file)) {
                return $this->raiseError(sprintf(
                        '%s [%s on line %d]', $e->getMessage(), __FILE__, __LINE__
                    ),
                    TRANSLATION2_ERROR
                );
            }
            return $this->raiseError(sprintf(
                    'Cannot find file "%s" [%s on line %d]',
                    $file, __FILE__, __LINE__
                ),
                TRANSLATION2_ERROR_CANNOT_FIND_FILE
            );
        }
        
        $this->cachedDomains[$curLang][$pageID] = $gtFile->strings;
        $this->_switchLang($oldLang);
        return $gtFile->strings;
    }

    // }}}
    // {{{ getOne()

    /**
     * Get a single item from the container, without caching the whole page
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @return string
     */
    function getOne($stringID, $pageID = null, $langID = null)
    {
        // native mode
        if ($this->_native) {
            $oldLang = $this->_switchLang($langID);
            $curLang = $this->currentLang['id'];

            if (empty($pageID) || $pageID == TRANSLATION2_DEFAULT_PAGEID) {
                $pageID = $this->options['default_domain'];
            }
            
            $string = dgettext($pageID, $stringID);

            $this->_switchLang($oldLang);
            return $string;
        }
        
        // use File_Gettext
        $page = $this->getPage($pageID, $langID);
        
        // return original string if there's no translation available
        if (isset($page[$stringID]) && strlen($page[$stringID])) {
            return $page[$stringID];
        } else {
            return $stringID;
        }
    }

    // }}}
    // {{{ getStringID()

    /**
     * Get the stringID for the given string
     *
     * @param string $stringID
     * @param string $pageID
     * @return string|PEAR_Error
     */
    function getStringID($string, $pageID = null)
    {
        if (empty($pageID) || $pageID == TRANSLATION2_DEFAULT_PAGEID) {
            $pageID = $this->options['default_domain'];
        }
        
        if (!array_key_exists($pageID, $this->_domains)) {
            return $this->raiseError(sprintf(
                    'The domain "%s" was not specified in the domains '.
                    'INI file "%s" [%s on line %d]', $pageID, 
                    $this->options['domains_path_file'], __FILE__, __LINE__
                ),
                TRANSLATION2_ERROR_DOMAIN_NOT_SET
            );
        }

        return array_search($string, $this->getPage($pageID));
    }

    // }}}
}
?>