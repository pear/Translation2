<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Lorenzo Alberton <l dot alberton at quipo dot it>            |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'Translation2'.DIRECTORY_SEPARATOR.'Container.php';

/**
 * Storage driver for fetching data with gettext
 *
 * =============================
 * langs.ini file format example
 * =============================
 * [en_UK]
 * id = en_UK
 * name = English
 * meta = iso-8859-1
 * error_text = not available in English
 *
 * [it]
 * id = it
 * name = italiano
 * meta = iso-8859-1
 * error_text = non disponibile in italiano
 *
 *
 * ===============================
 * domains.ini file format example
 * ===============================
 * messages = /usr/data/locale
 * errors = /usr/data/locale
 * myApp =  /usr/data/locale
 * myOtherApp = /usr/newData/locale
 *
 *
 *
 *
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
        $this->_domains = @parse_ini_file($this->options['domains_path_file']);
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
       //$this->options['path_to_locale']    = './';
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     *
     * @return array
     */
    function fetchLangs()
    {
        $this->langs = parse_ini_file($this->options['langs_avail_file'], true);
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
        $this->currentLang = $this->langs[$langID];
        putenv("LANG=".$this->currentLang['id']);
        setlocale(LC_ALL, $this->currentLang['id']);
        return $this->langs[$langID];
    }

    // }}}
    // {{{ getPage()

    /**
     * Does anyone know how to get all the strings from a domain?
     * @param string $pageID
     * @return array
     */
    function getPage($pageID=null)
    {
        //if (array_key_exists($pageID, $this->cachedDomains)) {
        if (array_key_exists($this->currentLang['id'], $this->cachedDomains) &&
            array_key_exists($pageID, $this->cachedDomains[$this->currentLang['id']])
        ) {
            return $this->cachedDomains[$this->currentLang['id']][$pageID];
        }

        require_once 'Translation2'.DIRECTORY_SEPARATOR.'Utils.php';
        if (is_null($pageID)) {
            $pageID = $this->options['default_domain'];
        }
        $domainPath = $this->_domains[$this->currentLang['id']]
                    . DIRECTORY_SEPARATOR.$this->currentLang['id']
                    . DIRECTORY_SEPARATOR.'LC_MESSAGES';
        $this->cachedDomains[$this->currentLang['id']][$pageID] =
                    Translation2_Utils::po_parser($domainPath, $pageID);
        return $this->cachedDomains[$this->currentLang['id']][$pageID];
    }

    // }}}
    // {{{ getOne()

    /**
     * Get a single item from the container, without caching the whole page
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @return string
     */
    function getOne($stringID, $pageID=null, $langID=null)
    {
        //debug('====== IN GET ONE!!!! ======');
        $langChanged = false;
        if (!is_null($langID) && ($langID != $this->currentLang['id'])) {
            $bkpLang = $this->currentLang['id'];
            $langChanged = true;
            $this->setLang($langID);
        }

        if (is_null($pageID)) {
            textdomain($this->options['default_domain']);
        } else {
            textdomain($pageID);
        }

        $string = gettext($stringID);

        if ($langChanged) {
            $this->setLang($bkpLang);
        }

        return $string;
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
        if (empty($pageID)) {
            $pageID = $this->options['default_domain'];
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