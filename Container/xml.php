<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is available through the world-wide-web at                      |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Olivier Guilyardi <olivier at samalyse dot com>             |
// |          Lorenzo Alberton <l dot alberton at quipo dot it>           |
// +----------------------------------------------------------------------+
//
// $Id$
//
/**
 * @package Translation2
 * @version $Id $
 */

/**
 * require Translation2_Container class
 */
require_once 'Translation2/Container.php';
/**
 * require XML_Unserializer class
 */
require_once 'XML/Unserializer.php';

/**
 * Storage driver for fetching data from an xml file 
 *
 * Example file :
 * 
 * <?xml version="1.0" encoding="iso-8859-1"?>
 * <translation2>
 *     <languages>
 *         <lang id='fr_FR'>
 *             <name> English </name>
 *             <meta> Custom meta data</meta>
 *             <error_text> Non disponible en français </error_text>
 *             <encoding> iso-8859-1 </encoding>
 *         </lang>
 *         <!-- some more <lang>...</lang> -->
 *     </languages>
 *     <pages>
 *         <page key='pets'>
 *             <string key='cat'>
 *                 <tr lang='fr_FR'> Chat </tr>
 *                 <!-- some more <tr>...</tr> -->
 *             </string>
 *             <!-- some more <string>...</string> -->
 *         </page>
 *         <!-- some more <page>...</page> -->
 *     </pages>
 * </translation2>
 *
 * @package  Translation2
 * @version  $Revision $
 */
class Translation2_Container_xml extends Translation2_Container
{
    // {{{ class vars

    /**
     * Unserialized XML data 
     * @var object
     */
    var $_data = null;

    /**
     * XML file name
     * @var string
     */
    var $_filename;
    
    // }}}
    // {{{ init

    /**
     * Initialize the container 
     *
     * @param  string  $filename Path to the XML file
     * @return boolean|PEAR_Error object if something went wrong
     */
    function init($options)
    {
        $this->_filename = $options['filename'];
        unset($options['filename']);
        $this->_setDefaultOptions();
        $this->_parseOptions($options);

        return $this->_loadFile();
    }

    // }}}
    // {{{ _loadFile()
    
    /**
     * Load an XML file into memory, and eventually decode the strings from UTF-8
     *
     * @access private
     * @return boolean|PEAR_Error
     */
    function _loadFile()
    {
        $keyAttr = array (
            'lang'   => 'id',
            'page'   => 'key',
            'string' => 'key',
            'tr'     => 'lang'
        );
        $unserializer = &new XML_Unserializer (array('keyAttribute' => $keyAttr));
        if (PEAR::isError($status = $unserializer->unserialize($this->_filename, true))) {
            return $status;
        }

        // unserialize data
        $this->_data = $unserializer->getUnserializedData();

        // Handle default language settings.
        // This allows, for example, to rapidly write the meta data as:
        //
        // <lang key="fr"/>
        // <lang key="en"/>

        $defaults = array(
            'name'       => '',
            'meta'       => '',
            'error_text' => '',
            'encoding'   => 'iso-8859-1'
        );

        foreach ($this->_data['languages'] as $lang_id => $settings) {
            if (empty($settings)) {
                $this->_data['languages'][$lang_id] = $defaults;
            } else {
                $this->_data['languages'][$lang_id] =
                    array_merge($defaults, $this->_data['languages'][$lang_id]);
            }
        }

        // convert lang metadata from UTF-8
        if (PEAR::isError($e = $this->_convertLangEncodings('from_xml'))) {
            return $e;
        }

        // convert encodings of the translated strings from xml (somehow heavy)
        return $this->_convertEncodings('from_xml');
    }
    
    // }}}
    // {{{ _convertEncodings()

    /** 
     * Convert strings to/from XML unique charset (UTF-8)
     *
     * @param string ['from_xml' | 'to_xml']
     * @return boolean|PEAR_Error
     */
    function _convertEncodings($direction) 
    {
        if ($direction == 'from_xml') {
            $source_encoding = 'UTF-8';
        } else {
            $target_encoding = 'UTF-8';
        }
        
        foreach ($this->_data['pages'] as $page_id => $page_content) {
            foreach ($page_content as $str_id => $translations) {
                foreach ($translations as $lang => $str) {
                    if ($direction == 'from_xml') {
                        $target_encoding =
                            strtoupper($this->_data['languages'][$lang]['encoding']);
                    } else {
                        $source_encoding =
                            strtoupper($this->_data['languages'][$lang]['encoding']);
                    }
                    if ($target_encoding != $source_encoding) {
                        $res = iconv ($source_encoding, $target_encoding, $str);
                        if ($res === false) {
                            $msg = 'Encoding conversion error ' .
                                   "(source encoding: $source_encoding, ".
                                   "target encoding: $target_encoding, ".
                                   "processed string: \"$str\"";
                            return $this->raiseError($msg,
                                    TRANSLATION2_ERROR_ENCODING_CONVERSION,
                                    PEAR_ERROR_RETURN,
                                    E_USER_WARNING);
                        }
                        $this->_data['pages'][$page_id][$str_id][$lang] = $res;
                    }
                }
            }
        }
        return true;
    }
         
    // }}}
    // {{{ _convertLangEncodings()

    /**
     * Convert lang data to/from XML unique charset (UTF-8)
     *
     * @param string $direction   ['from_xml' | 'to_xml']
     * @return boolean|PEAR_Error
     */
    function _convertLangEncodings($direction)
    {
        static $fields = array('name', 'meta', 'error_text');

        if ($direction == 'from_xml') {
            $source_encoding = 'UTF-8';
        } else {
            $target_encoding = 'UTF-8';
        }
        
        foreach ($this->_data['languages'] as $lang_id => $lang) {
            if ($direction == 'from_xml') {
                $target_encoding = strtoupper($lang['encoding']);
            } else {
                $source_encoding = strtoupper($lang['encoding']);
            }
            //foreach (array_keys($lang) as $field) {
            foreach ($fields as $field) {
                if ($target_encoding != $source_encoding && !empty($lang[$field])) {
                    $res = iconv ($source_encoding, $target_encoding, $lang[$field]);
                    if ($res === false) {
                        $msg = 'Encoding conversion error ' .
                               "(source encoding: $source_encoding, ".
                               "target encoding: $target_encoding, ".
                               "processed string: \"$lang[$field]\"";
                        return $this->raiseError($msg,
                                TRANSLATION2_ERROR_ENCODING_CONVERSION,
                                PEAR_ERROR_RETURN,
                                E_USER_WARNING);
                    }
                    $this->_data['languages'][$lang_id][$field] = $res;
                }
            }
        }
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
        //save changes on shutdown or in real time?
        $this->options['save_on_shutdown']  = true;
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs
     */
    function fetchLangs()
    {
        $res = array();
        foreach ($this->_data['languages'] as $id => $spec) {
            $spec['id'] = $id;
            $res[$id] = $spec;
        }
        $this->langs = $res;
    }

    // }}}
    // {{{ getPage()

    /**
     * Returns an array of the strings in the selected page
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getPage($pageID = null, $langID = null)
    {
        $langID = is_null($langID)   ? $this->currentLang['id'] : $langID;
        $pageID = (is_null($pageID)) ? '#NULL'  : $pageID;
        $pageID = (empty($pageID))   ? '#EMPTY' : $pageID;

        $result = array();
        foreach ($this->_data['pages'][$pageID] as $str_id => $translations) {
            $result[$str_id]  = isset($translations[$langID]) 
                                ? $translations[$langID] 
                                : null;
        }
        
        return $result;
    }

    // }}}
    // {{{ getOne()

    /**
     * Get a single item from the container
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @return string
     */
    function getOne($stringID, $pageID = null, $langID = null)
    {
        $langID = is_null($langID) ? $this->currentLang['id'] : $langID;
        $pageID = (is_null($pageID)) ? '#NULL' : $pageID;                         

        return isset($this->_data['pages'][$pageID][$stringID][$langID])
               ? $this->_data['pages'][$pageID][$stringID][$langID]
               : null;
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
    function getStringID($string, $pageID = null)
    {
        $pageID = (is_null($pageID)) ? '#NULL' : $pageID;                        
        
        foreach ($this->_data['pages'][$pageID] as $str_id => $translations) {
            if (array_search($string,$translations) !== false) {
                return $str_id;
            }
        }

        return '';
    }
    
    // }}}
}
?>