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
     * Additional options for the storage container
     * @var array
     */
    var $options = array();

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

    // }}}
    // {{{ Constructor

    /**
     * Constructor of the container class
     *
     * Initate connection to the database via PEAR::DB
     *
     * @param  array  gettext parameters
     * @return object Returns an error object if something went wrong
     */
    function Translation2_Container_gettext($options)
    {
        $this->_setDefaults();
        $this->_parseOptions($options);

        $this->_domains = @parse_ini_file($this->options['domains_path_file']);
        if ($this->_domains === false) {
            return $this->raiseError(
                'cannot find file '.$this->options['domains_path_file']
                .' in '.__FILE__.' on line '.__LINE__,
                TRANSLATION2_ERROR_CANNOT_FIND_FILE
            );
        }

        foreach ($this->_domains as $domain => $path) {
            bindtextdomain($domain, $path);
        }
    }

    // }}}
    // {{{ _setDefaults()

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaults()
    {
        $this->options['langs_avail_file']  = 'langs.ini';
        $this->options['domains_path_file'] = 'domains.ini';
        $this->options['default_domain']    = 'messages';
        $this->options['carriage_return']   = "\n";
        //$this->options['path_to_locale']  = './';
    }

    // }}}
    // {{{ switchLang()

    /**
     * @param string new langID
     * @return string previous lang
     */
    function switchLang($langID)
    {
        if (is_null($langID) || ($langID == $this->currentLang['id'])) {
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
        $this->langs = parse_ini_file($this->options['langs_avail_file'], true);

        // maps en to en_US or whatever the user defines
        foreach ($this->langs as $id => $lang) {
            if (isset($lang['use']) && isset($this->langs[$lang['use']])) {
                $this->langs[$id] = $this->langs[$lang['use']];
            }
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
        // prepare i.e. en-us to en_US
        if (strlen($langID) > 2) {
            list($lang, $country) = preg_split('/[_-]/', $langID, 1);
            $langID = strtolower($lang) . '_' . strtoupper($country);
        } else {
            $langID = strtolower($langID);
        }

        $this->getLangs(); //load available languages, if not loaded yet (ignore return value)
        $this->currentLang = $this->langs[$langID];

        if (OS_WINDOWS) {
            $locale   = $this->currentLang['windows'];
            $language = substr($this->currentLang['id'], 0, 2);
        } else {
            $locale = $language = $this->currentLang['id'];
        }

        // satisfy gettext
        putenv('LANG=' . $language);
        putenv('LANGUAGE=' . $language);
        // set corresponding locale
        setLocale(LC_ALL, $locale);

        return $this->langs[$langID];
    }

    // }}}
    // {{{ getPage()

    /**
     * Get all the strings from a domain (parsing the .mo file)
     *
     * @param string $pageID
     * @return array
     */
    function getPage($pageID=null, $langID=null)
    {
        $oldLang = $this->switchLang($langID);
        if (array_key_exists($this->currentLang['id'], $this->cachedDomains) &&
            array_key_exists($pageID, $this->cachedDomains[$this->currentLang['id']])
        ) {
            return $this->cachedDomains[$this->currentLang['id']][$pageID];
        }

        require_once 'File/Gettext/MO.php';

        if (is_null($pageID)) {
            $pageID = $this->options['default_domain'];
        }
        if (!array_key_exists($pageID, $this->_domains)) {
            return $this->raiseError(
                'the domain "'.$pageID.'" was not specified in the .ini file'
                .' ['.__FILE__.' on line '.__LINE__.']',
                TRANSLATION2_ERROR_DOMAIN_NOT_SET
            );
        }
        $domainPath = $this->_domains[$pageID]
                    . DIRECTORY_SEPARATOR.$this->currentLang['id']
                    . DIRECTORY_SEPARATOR.'LC_MESSAGES';
        $filename = $domainPath.DIRECTORY_SEPARATOR.$pageID.'.mo';
        if (!file_exists($filename)) {
            return $this->raiseError(
                'cannot find file '.$filename .' ['.__FILE__.' on line '.__LINE__.']',
                TRANSLATION2_ERROR_CANNOT_FIND_FILE
            );
        }

        $moFile = new File_Gettext_MO;
        $err = $moFile->load($filename);
        if (PEAR::isError($err)) {
            return $err;
        }
        $contents = $moFile->toArray();
        $this->cachedDomains[$this->currentLang['id']][$pageID] = $contents['strings'];
        $langID = $this->switchLang($oldLang);
        return $this->cachedDomains[$langID][$pageID];
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
    function getOne($stringID, $pageID=null, $langID=null)
    {
        $oldLang = $this->switchLang($langID);

        if (is_null($pageID)) {
            textdomain($this->options['default_domain']);
        } else {
            textdomain($pageID);
        }

        $string = gettext($stringID);

        $this->switchLang($oldLang);
        return $string;
    }

    // }}}
    // {{{ getStringID()

    /**
     * Get the stringID for the given string
     *
     * @param string $stringID
     * @param string $pageID
     * @return string
     */
    function getStringID($string, $pageID=null)
    {
        if (empty($pageID)) {
            $pageID = $this->options['default_domain'];
        }
        if (!array_key_exists($pageID, $this->_domains)) {
            return $this->raiseError(
                'the domain "'.$pageID.'" was not specified in the .ini file'
                .' ['.__FILE__.' on line '.__LINE__.']',
                TRANSLATION2_ERROR_DOMAIN_NOT_SET
            );
        }
        $this->getPage($pageID);

        return array_search(
            $string,
            $this->cachedDomains[$this->currentLang['id']][$pageID]
        );
    }

    // }}}
}
?>