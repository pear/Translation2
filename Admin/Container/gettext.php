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

require_once 'Translation2'.DIRECTORY_SEPARATOR.'Container'.DIRECTORY_SEPARATOR.'gettext.php';

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
     * @param string $langID
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langID, $path=null)
    {
        if (is_null($path)) {
            //use the path of the first domain
            $path = reset($this->_domains);
        }
        if (!is_dir($path)) {
            return $this->raiseError(
                'the specified path ("'.$path.'") is not valid',
                TRANSLATION2_ERROR_INVALID_PATH
            );
        }
        $path .= DIRECTORY_SEPARATOR.$langID;
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return $this->raiseError(
                    'cannot create directory "'.$path.'"',
                    TRANSLATION2_ERROR_CANNOT_CREATE_DIR
                );
            }
        }
        $path .= DIRECTORY_SEPARATOR.'LC_MESSAGES';
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return $this->raiseError(
                    'cannot create directory "'.$path.'"',
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
        $langs = parse_ini_file($this->options['langs_avail_file'], true);
        if (in_array($langData['lang_id'], array_keys($this->langs))) {
            return true;
        }

        $valid_keys = array(
            'name',
            'meta',
            'error_text',
            'windows'
        );

        $langs[$langData['lang_id']] = array();
        $langs[$langData['lang_id']]['id']      = $langData['lang_id'];
        if (array_key_exists('use', $langData)) {
            $langs[$langData['lang_id']]['use'] = $langData['use'];
        } else {
            foreach ($valid_keys as $valid_key) {
                $langs[$langData['lang_id']][$valid_key] =
                    isset($langData[$valid_key]) ? $langData[$valid_key] : '';
            }
        }

        //write to file
        $fp = fopen($this->options['langs_avail_file'], 'w');
        foreach ($langs as $key => $lang) {
            fwrite($fp, '[' . $key . ']' . $this->options['carriage_return']);
            if (array_key_exists('use', $lang)) {
                fwrite($fp, 'use = ' . $lang['use'] . $this->options['carriage_return']);
            } else {
                fwrite($fp, 'id = '  . $lang['id']  . $this->options['carriage_return']);
            }
            foreach ($valid_keys as $valid_key) {
                if (array_key_exists($valid_key, $lang)) {
                    fwrite($fp, $valid_key .' = '. $lang[$valid_key] . $this->options['carriage_return']);
                }
            }
            fwrite($fp, $this->options['carriage_return']);
        }
        fclose($fp);
        return true;
    }

    // }}}
    // {{{ add()

    /**
     * Add a new entry in the strings domain.
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $stringArray Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function add($stringID, $pageID, $stringArray)
    {
        $langs = array_keys($stringArray);
        $numLangs = count($langs);
        $availableLangs = $this->getLangs('ids');
        foreach ($langs as $key => $langID) {
            if (!in_array($langID, $availableLangs)) {
                unset($langs[$key]);
            }
        }

        if (!count($langs)) {
            //return error: no valid lang provided
            return true;
        }

        require_once 'File'.DIRECTORY_SEPARATOR.'Gettext'.DIRECTORY_SEPARATOR.'MO.php';

        foreach ($langs as $langID) {
            $moFile = new File_Gettext_MO;
            $domainPath = $this->_domains[$pageID]
                    . DIRECTORY_SEPARATOR.$langID
                    . DIRECTORY_SEPARATOR.'LC_MESSAGES';
            $filename = $domainPath.DIRECTORY_SEPARATOR.$pageID.'.mo';
            if (!file_exists($filename)) {
                //create an empty .mo file
                $moFile->fromArray(
                    array(
                        'meta'    => array(),
                        'strings' => array()
                    )
                );
                $moFile->save($filename);
            }

            $err = $moFile->load($filename);
            if (PEAR::isError($err)) {
                return $err;
            }

            $moFile->strings[$stringID] = $stringArray[$langID];
            $moFile->save($filename);
            unset($moFile);
        }
        return true;
    }

    // }}}
    // {{{ remove()

    /**
     * Remove an entry from the strings domain.
     *
     * @param string $stringID
     * @param string $pageID
     * @return mixed true on success, PEAR_Error on failure
     */
    function remove($stringID, $pageID)
    {
        $langs = $this->getLangs('ids');
        $tables = array();

        require_once 'File'.DIRECTORY_SEPARATOR.'Gettext'.DIRECTORY_SEPARATOR.'MO.php';
        $moFile = new File_Gettext_MO;

        foreach ($langs as $langID) {
            $domainPath = $this->_domains[$pageID]
                    . DIRECTORY_SEPARATOR.$langID
                    . DIRECTORY_SEPARATOR.'LC_MESSAGES';
            $filename = $domainPath.DIRECTORY_SEPARATOR.$pageID.'.mo';
            if (!file_exists($filename)) {
                continue;  //nothing to do
            }

            $err = $moFile->load($filename);
            if (PEAR::isError($err)) {
                return $err;
            }

            if (array_key_exists($stringID, $moFile->strings)) {
                unset($moFile->strings[$stringID]);
                $moFile->save($filename);
            }
        }
        unset($moFile);
        return true;
    }

    // }}}
}
?>