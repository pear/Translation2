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
// | Author: Olivier Guilyardi <olivier at samalyse dot com>              |
// +----------------------------------------------------------------------+
//
// $Id$
//
/**
 * @package Translation2
 * @version $Id$
 */

/**
 * require Translation2_Container_xml class
 */
require_once 'Translation2/Container/xml.php';

require_once "XML/Util.php";

/**
 * Storage driver for storing/fetching data to/from an XML file
 *
 * @package  Translation2
 * @version  $Revision $
 */
class Translation2_Admin_Container_xml extends Translation2_Container_xml
{

    // {{{ class vars

    /**
     * File resource. Opened in write mode when saving is scheduled.
     * @var resource
     */
    var $_fileRes = null;
    
    // }}}
    // {{{ createNewLang()

    /**
     * Does nothing (here for compatibility with the container interface)
     *
     * @param array $langData
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langData)
    {
        return true;
    }

    // }}}
    // {{{ addLangToAvailList()

    /**
     * Creates a new entry in the <languages> section
     *
     * @param array $langData array('lang_id'    => 'en',
     *                              'name'       => 'english',
     *                              'meta'       => 'some meta info',
     *                              'error_text' => 'not available');
     * @return mixed true on success, PEAR_Error on failure
     */
    function addLangToAvailList($langData)
    {
        $validInput = array(
            'name' => '',
            'meta' => 'iso-8859-1',
            'error_text' => ''
        );
        
        foreach ($validInput as $key => $val) {
            if (isset($langData[$key])) $validInput[$key] = $langData[$key];
        }
        
        $this->_data['languages'][$langData['lang_id']] = $validInput;

        return $this->_scheduleSaving();
    }

    // }}}
    // {{{ add()

    /**
     * Add a new entry in the strings table.
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $stringArray Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function add($stringID, $pageID, $stringArray)
    {
        $langs = array_intersect(
            array_keys($stringArray),
            $this->getLangs('ids')
        );

        $pageID = is_null($pageID) ? '#NULL' : $pageID;
        $pageID = empty($pageID) ? '#EMPTY' : $pageID;
        foreach ($langs as $lang) {
            $this->_data['pages'][$pageID][$stringID][$lang] = $stringArray[$lang];
        }
        
       return $this->_scheduleSaving();
    }

    // }}}
    // {{{ update()

    /**
     * Update an existing entry in the strings table.
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $stringArray Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function update($stringID, $pageID, $stringArray)
    {
        return $this->add($stringID, $pageID, $stringArray);
    }

    // }}}
    // {{{ remove()

    /**
     * Remove an entry from the strings table.
     *
     * @param string $stringID
     * @param string $pageID
     * @return mixed true on success, PEAR_Error on failure
     */
    function remove($stringID, $pageID)
    {
        $pageID = is_null($pageID) ? '#NULL' : $pageID;
        $pageID = empty($pageID) ? '#EMPTY' : $pageID;

        unset ($this->_data['pages'][$pageID][$stringID]);

        return $this->_scheduleSaving();
    }

    // }}}
    // {{{ _scheduleSaving()
    
    /**
     * Prepare data saving
     *
     * This methods registers _saveData() as a PEAR shutdown function. This
     * is to avoid saving multiple times if the programmer makes several 
     * changes.
     * 
     * @return mixed true on success or a PEAR_Error on failure
     * @access private
     * @see Translation2_Admin_Container_xml::_saveData()
     */
    function _scheduleSaving()
    {
        if (is_null($this->_fileRes)) {
            if (!$this->_fileRes = fopen ($this->_filename, 'w')) {
                return new PEAR_Error ('Unable to open the XML file for writing : '.
                                       $this->_filename);
            }
            register_shutdown_function (array(&$this, '_saveData'));
        }
        return true;
    }

    // }}}
    // {{{ _saveData()
    
    /**
     * Serialize and save the updated tranlation data to the XML file
     *
     * @return void
     * @access private
     * @see Translation2_Admin_Container_xml::_scheduleSaving()
     */
    function _saveData()
    {
        $this->_convertEncodings('to_xml');
        
        // Serializing
        
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n" .
               "<!DOCTYPE translation2 [\n" .
               "  <!ELEMENT translation2 (languages,pages)>\n" .
               "  <!ELEMENT languages (lang*)>\n" .
               "  <!ELEMENT lang (name?,meta?,error_text?,encoding?)>\n" .
               "  <!ATTLIST lang id ID #REQUIRED>\n" .
               "  <!ELEMENT name (#PCDATA)>\n" .
               "  <!ELEMENT meta (#PCDATA)>\n" .
               "  <!ELEMENT error_text (#PCDATA)>\n" .
               "  <!ELEMENT encoding (#PCDATA)>\n" .
               "  <!ELEMENT pages (page*)>\n" .
               "  <!ELEMENT page (string*)>\n" .
               "  <!ATTLIST page key CDATA #REQUIRED>\n" .
               "  <!ELEMENT string (tr*)>\n" .
               "  <!ATTLIST string key CDATA #REQUIRED>\n" .
               "  <!ELEMENT tr (#PCDATA)>\n" .
               "  <!ATTLIST tr lang IDREF #REQUIRED>\n" .
               "]>\n\n" .
               "<translation2>\n" .
               "  <languages>\n";

        foreach ($this->_data['languages'] as $lang => $spec) {
            extract ($spec);
            $xml .= "    <lang id=\"$lang\">\n" .
                    "      <name>" . 
                    ($name ? ' ' . XML_Util::replaceEntities($name) . ' ' : '') . 
                    "</name>\n" .
                    "      <meta>" . 
                    ($meta ? ' ' . XML_Util::replaceEntities($meta) . ' ' : "") . 
                    "</meta>\n" .
                    "      <error_text>" . 
                    ($error_text 
                        ? ' ' . XML_Util::replaceEntities($error_text) . ' ' 
                        : "") . 
                    "</error_text>\n" .
                    "      <encoding>" . ($encoding ? " $encoding " : "") . 
                    "</encoding>\n" .  
                    "    </lang>\n";
        }

        $xml .= "  </languages>\n" .
                "  <pages>\n";

        foreach ($this->_data['pages'] as $page => $strings) {
            $xml .= "    <page key=\"" . XML_Util::replaceEntities($page) . 
                    "\">\n";
            foreach ($strings as $str_id => $translations) {
                $xml .= "      <string key=\"" . 
                        XML_Util::replaceEntities($str_id) . "\">\n";
                foreach ($translations as $lang => $str) {
                    $xml .= "        <tr lang=\"$lang\"> " .
                            XML_Util::replaceEntities($str) . " </tr>\n";
                }
                $xml .= "      </string>\n";
            }
            $xml .= "    </page>\n";
        }

        $xml .= "  </pages>\n" .
                "</translation2>\n";

        // Saving
        fwrite ($this->_fileRes, $xml);
        fclose ($this->_fileRes);
    }

    // }}}
}
?>
