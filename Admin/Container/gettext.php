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
 * require Translation2_Container_gettext class
 */
require_once 'Translation2/Container/gettext.php';

/**
 * Storage driver for storing/fetching data to/from a gettext file
 *
 * This storage driver requires gettext extension
 *
 * @package  Translation2
 * @version  $Revision$
 */
class Translation2_Admin_Container_gettext extends Translation2_Container_gettext
{
    // {{{ class vars

    var $_bulk = false;
    var $_queue = array();
    var $_fields = array('name', 'meta', 'error_text', 'encoding');

    // }}}
    // {{{ createNewLang()

    /**
     * Creates a new entry in the langs_avail .ini file.
     *
     * @param   array   $langData
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function createNewLang($langData, $path = null)
    {
        if (!isset($path)) {
            $path = $this->_domains[$this->options['default_domain']];
        }
        
        $path .= '/'. $langData['lang_id'] . '/LC_MESSAGES';
        
        if (!is_dir($path)) {
            require_once 'System.php';
            if (!System::mkdir(array('-p', $path))) {
                return $this->raiseError(sprintf(
                        'Cannot create new language in path "%s"', $path
                    ),
                    TRANSLATION2_ERROR_CANNOT_CREATE_DIR
                );
            }
        }
        
        return true;
    }

    // }}}
    // {{{ addLangToAvailList()

    /**
     * Creates a new entry in the langsAvail .ini file.
     * If the file doesn't exist yet, it is created.
     *
     * @param array $langData array('lang_id'    => 'en',
     *                              'name'       => 'english',
     *                              'meta'       => 'some meta info',
     *                              'error_text' => 'not available'
     *                              'encoding'   => 'iso-8859-1',
     * );
     * @return mixed true on success, PEAR_Error on failure
     */
    function addLangToAvailList($langData)
    {
        if (PEAR::isError($changed = $this->_updateLangData($langData))) {
            return $changed;
        }
        return $changed ? $this->_writeLangsAvailFile() : true;
    }

    // }}}
    // {{{ add()

    /**
     * Add a new entry in the strings domain.
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $strings Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function add($stringID, $pageID, $strings)
    {
        if (!isset($pageID)) {
            $pageID = $this->options['default_domain'];
        }
        
        $langs = array_intersect(array_keys($strings), $this->getLangs('ids'));
        
        if (!count($langs)) {
            return true; // really?
        }
        
        if ($this->_bulk) {
            foreach ($strings as $lang => $string) {
                if (in_array($lang, $langs)) {
                    $this->_queue['add'][$pageID][$lang][$stringID] = $string;
                }
            }
            return true;
        } else {
            $add = array();
            foreach ($strings as $lang => $string) {
                if (in_array($lang, $langs)) {
                    $add[$pageID][$lang][$stringID] = $string;
                }
            }
            return $this->_add($add);
        }
    }

    // }}}
    // {{{ remove()

    /**
     * Remove an entry from the domain.
     *
     * @param string $stringID
     * @param string $pageID
     * @return mixed true on success, PEAR_Error on failure
     */
    function remove($stringID, $pageID)
    {
        if (!isset($pageID)) {
            $pageID = $this->options['default_domain'];
        }
        
        if ($this->_bulk) {
            $this->_queue['remove'][$pageID][$stringID] = true;
            return true;
        } else {
            $tmp = array($pageID => array($stringID => true));
            return $this->_remove($tmp);
        }
        
    }

    // }}}
    // {{{ update()
    
    /**
     * Update
     * 
     * Alias for Translation2_Admin_Container_gettext::add()
     * 
     * @see add()
     * 
     * @access  public
     * @return  mixed
     * @param   string  $stringID
     * @param   string  $pageID
     * @param   array   $strings
     */
    function update($stringID, $pageID, $strings)
    {
        return $this->add($stringID, $pageID, $strings);
    }
    
    // }}}
    // {{{ removeLang()
    
    /**
     * Remove Language
     * 
     * @access  public
     * @return  true|PEAR_Error
     * @param   string  $langID
     * @param   bool    $force  (unused)
     */
    function removeLang($langID, $force = false)
    {
        require_once 'System.php';
        foreach ((array) $this->_domains as $domain => $path) {
            if (is_dir($fp = $path .'/'. $langID)) {
                if (PEAR::isError($e = System::rm(array('-rf', $fp))) || !$e) {
                    return $e ? $e : PEAR::raiseError(sprintf(
                            'Could not remove language "%s" from domain "%s" '.
                            'in path "%s" (probably insufficient permissions)',
                            $langID, $domain, $path
                        ),
                        TRANSLATION2_ERROR
                    );
                }
            }
        }
        return true;
    }
    
    // }}}
    // {{{ updateLang()
    
    /**
     * Update the lang info in the langs_avail file
     * 
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     * @param   array   $langData
     */
    function updateLang($langData)
    {
        if (PEAR::isError($changed = $this->_updateLangData($langData))) {
            return $changed;
        }
        return $changed ? $this->_writeLangsAvailFile() : true;
    }
    
    // }}}
    // {{{ getPageNames()

    /**
     * Get a list of all the domains
     *
     * @access  public
     * @return  array
     */
    function getPageNames()
    {
        return array_keys($this->_domains);
    }

    // }}}
    // {{{ begin()
    
    /**
     * Begin
     * 
     * @access  public
     * @return  void
     */
    function begin()
    {
        $this->_bulk = true;
    }
    
    // }}}
    // {{{ commit()
    
    /**
     * Commit
     * 
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function commit()
    {
        $this->_bulk = false;
        if (isset($this->_queue['remove'])) {
            if (PEAR::isError($e = $this->_remove($this->_queue['remove']))) {
                return $e;
            }
        }
        if (isset($this->_queue['add'])) {
            if (PEAR::isError($e = $this->_add($this->_queue['add']))) {
                return $e;
            }
        }
        return true;
    }
    
    // }}}
    // {{{ _add()
    
    /**
     * Add
     * 
     * @access  private
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function _add(&$bulk)
    {
        require_once 'File/Gettext.php';
        $gtFile = &File_Gettext::factory($this->options['file_type']);
        $langs  = $this->getLangs('array');
        
        foreach ((array) $bulk as $pageID => $languages) {
            //create the new domain on demand
            if (!isset($this->_domains[$pageID])) {
                if (PEAR::isError($e = $this->_addDomain($pageID))) {
                    return $e;
                }
            }
            $path = $this->_domains[$pageID];
            if ($path[strlen($path)-1] != '/' && $path[strlen($path)-1] != '\\') {
                $path .= '/';
            }
            $file = '/LC_MESSAGES/'. $pageID .'.'. $this->options['file_type'];
            
            foreach ($languages as $lang => $strings) {
            
                if (is_file($path . $lang . $file)) {
                    if (PEAR::isError($e = $gtFile->load($path . $lang . $file))) {
                        return $e;
                    }
                }
                
                if (!isset($gtFile->meta['Content-Type'])) {
                    $gtFile->meta['Content-Type'] = 'text/plain; charset=';
                    if (isset($langs[$lang]['encoding'])) {
                        $gtFile->meta['Content-Type'] .= $langs[$lang]['encoding'];
                    } else {
                        $gtFile->meta['Content-Type'] .= $this->options['default_encoding'];
                    }
                }
    
                foreach ($strings as $stringID => $string) {
                    $gtFile->strings[$stringID] = $string;
                }

                if (PEAR::isError($e = $gtFile->save($path . $lang . $file))) {
                    return $e;
                }
                
                //refresh cache
                $this->cachedDomains[$lang][$pageID] = $gtFile->strings;
            }
        }
        
        $bulk = null;
        return true;
    }
    
    // }}}
    // {{{ _remove()
    
    /**
     * Remove
     * 
     * @access  private
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function _remove(&$bulk)
    {
        require_once 'File/Gettext.php';
        $gtFile = &File_Gettext::factory($this->options['file_type']);
        
        foreach ($this->getLangs('ids') as $lang) {
            
            foreach ((array) $bulk as $pageID => $stringIDs) {
                
                $file = sprintf(
                    '%s/%s/LC_MESSAGES/%s.%s',
                    $this->_domains[$pageID],
                    $lang,
                    $pageID,
                    $this->options['file_type']
                );
                
                if (is_file($file)) {
                    if (PEAR::isError($e = $gtFile->load($file))) {
                        return $e;
                    }
                    
                    foreach (array_keys($stringIDs) as $stringID) {
                        unset($gtFile->strings[$stringID]);
                    }
                    
                    if (PEAR::isError($e = $gtFile->save($file))) {
                        return $e;
                    }
                }
                
                //refresh cache
                $this->cachedDomains[$lang][$pageID] = $gtFile->strings;
            }
        }
        
        $bulk = null;
        return true;
    }
    
    // }}}
    // {{{ _addDomain()
    
    /**
     * Add the path-to-the-new-domain to the domains-path-INI-file
     *
     * @access  private
     * @param   $pageID string domain name
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function _addDomain($pageID)
    {
        $domain_path = count($this->_domains) ? reset($this->_domains) : 'locale/';
        
        if (!is_resource($f = fopen($this->options['domains_path_file'], 'a'))) {
            return $this->raiseError(sprintf(
                    'Cannot write to domains path INI file "%s"',
                    $this->options['domains_path_file']
                ),
                TRANSLATION2_ERROR_CANNOT_WRITE_FILE
            );
        }
        
        $CRLF = $this->options['carriage_return'];
        
        @flock($f, LOCK_EX);
        fwrite($f, $CRLF . $pageID . ' = ' . $domain_path . $CRLF);
        @flock($f, LOCK_UN);
        fclose($f);

        $this->_domains[$pageID] = $domain_path;

        return true;
    }
    
    // }}}
    // {{{ _writeLangsAvailFile()
    
    /**
     * Write the langs_avail INI file
     * 
     * @access  private
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function _writeLangsAvailFile()
    {
        if (PEAR::isError($langs = $this->getLangs())) {
            return $langs;
        }
        
        if (!is_resource($f = fopen($this->options['langs_avail_file'], 'w'))) {
            return $this->raiseError(sprintf(
                    'Cannot write to available langs INI file "%s"',
                    $this->options['langs_avail_file']
                ),
                TRANSLATION2_ERROR_CANNOT_WRITE_FILE
            );
        }
        $CRLF = $this->options['carriage_return'];

        @flock($f, LOCK_EX);
        
        foreach ($langs as $id => $data) {
            fwrite($f, '['. $id .']'. $CRLF);
            foreach ($this->_fields as $k) {
                if (isset($data[$k])) {
                    fwrite($f, $k . ' = ' . $data[$k] . $CRLF);
                }
            }
            fwrite($f, $CRLF);
        }
        
        @flock($f, LOCK_UN);
        fclose($f);
        return true;
    }
    
    // }}}
    // {{{ _updateLangData()
    
    /**
     * Update Lang Data
     * 
     * @access  private
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     * @param   array   $langData
     */
    function _updateLangData($langData)
    {
        if (PEAR::isError($langs = $this->getLangs())) {
            return $langs;
        }
        
        $lang     = &$langs[$langData['lang_id']];
        $changed  = false;
        foreach ($this->_fields as $k) {
            if (    isset($langData[$k]) && 
                    (!isset($lang[$k]) || $langData[$k] != $lang[$k])) {
                $lang[$k] = $langData[$k];
                $changed  = true;
            }
        }
        
        if ($changed) {
            $lang['id']  = $langData['lang_id'];
            $this->langs = $langs;
        }
        return $changed;
    }
    
    // }}}
}
?>
