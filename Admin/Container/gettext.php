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


    // }}}
    // {{{ createNewLang()

    /**
     * Creates a new entry in the langs_avail .ini file.
     *
     * @param array $langData
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langData, $path = null)
    {
        if (!isset($path)) {
            $path = $this->_domains[$this->options['default_domain']];
        }
        
        $path .= '/'. $langData['lang_id'] . '/LC_MESSAGES';
        
        if (!is_dir($path)) {
            require_once 'System.php';
            if (!System::mkdir(array('-p'. $path))) {
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
     *                              'windows'    => 'enu',
     *                              'error_text' => 'not available');
     * @return mixed true on success, PEAR_Error on failure
     */
    function addLangToAvailList($langData)
    {
        if (PEAR::isError($langs = $this->getLangs())) {
            return $langs;
        }
        if (isset($langs[$langData['lang_id']])) {
            return true;
        }
        
        $valid_keys = array(
            'name',
            'meta',
            'error_text',
            'windows'
        );
        
        $lang = &$langs[$langData['lang_id']];
        
        foreach (array('name', 'meta', 'error_text', 'windows') as $k) {
            $lang[$k] = isset($langData[$k]) ? $langData[$k] : '';
        }
        
        if (!is_resource($f = fopen($this->options['langs_avail_file'], 'w'))) {
            return $this->raiseError(sprintf(
                    'Cannot write to available langs INI file "%s"',
                    $this->options['langs_avail_file']
                ),
                TRANSLATION2_ERROR_CANNOT_WRITE_FILE
            );
        }
        @flock($f, LOCK_EX);
        
        $CRLF = $this->options['carriage_return'];
        foreach ($langs as $id => $data) {
            fwrite($f, '['. $id .']'. $CRLF);
            foreach (array('name', 'meta', 'error_text', 'windows') as $k) {
                if (isset($data[$k])) {
                    fwrite($f, $k .'='. $data[$k] . $CRLF);
                }
            }
            fwrite($f, $CRLF);
        }
        
        @flock($f, LOCK_UN);
        fclose($f);
        
        return true;
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
        
        $file = '/LC_MESSAGES/'. $pageID .'.mo';
        
        require_once 'File/Gettext.php';
        $MO = &File_Gettext::factory('MO');
        
        foreach ((array) $langs as $id) {
            $path = $this->_domains[$pageID] .'/'. $id;
            
            if (is_file($path . $file)) {
                if (PEAR::isError($e = $MO->load($path . $file))) {
                    return $e;
                }
            }

            $MO->strings[$stringID] = $strings[$id];
            if (PEAR::isError($e = $MO->save($path . $file))) {
                return $e;
            }
        }
        return true;
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
        
        $file = '/LC_MESSAGES/'. $pageID .'.mo';
        
        foreach ($this->getLangs('ids') as $lang) {
            $path = $this->_domains[$pageID] .'/'. $lang;
            
            if (is_file($path . $file)) {
                if (PEAR::isError($e = $MO->load($path . $file))) {
                    return $e;
                }
                unset($MO->strings[$stringID]);
                if (PEAR::isError($e = $MO->save($path . $file))) {
                    return $e;
                }
            }
        }
        return true;
    }

    // }}}
}
?>