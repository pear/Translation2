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
// | Author: Lorenzo Alberton <l dot alberton at quipo dot it>            |
// +----------------------------------------------------------------------+
//
// $Id$
//
/**
 * @package Translation2
 * @version $Id$
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
 * @see /docs/gettext_readme.txt for an usage example
 * @package  Translation2
 * @version  $Revision$
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
     * @var string
     * @access private
     */
    var $currentDomain = '';

    /**
     * @var array
     * @access private
     */
    var $cachedDomains = array();

    /**
     * @var boolean
     * @access private
     */
    var $_extensionLoaded = false;

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
        $this->_extensionLoaded = (
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
        if (!isset($langID)) {
            return $this->currentLang['id'];
        }
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
            $this->langs[$id] = $lang + array('id' => $id);
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
        
        if (!isset($pageID)) {
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
        if ($this->_extensionLoaded) {
            $oldLang = $this->_switchLang($langID);

            textdomain(isset($pageID) ? $pageID : $this->options['default_domain']);
            $string = gettext($stringID);

            $this->_switchLang($oldLang);
            return $string;
        }
        
        //gettext extension not loaded, use File_Gettext
        $page = $this->getPage($pageID, $langID);
        // return original string if no translation available
        return isset($page[$stringID]) ? $page[$stringID] : $stringID;
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
        if (empty($pageID)) {
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
