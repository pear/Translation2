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
 * Storage driver for fetching data from a database
 *
 * This storage driver can use all databases which are supported
 * by the PEAR MDB2 abstraction layer to fetch data.
 *
 * @package  Translation2
 * @version  $Revision$
 */
class Translation2_Container_mdb2 extends Translation2_Container
{

    // {{{ class vars

    /**
     * MDB2 object
     * @var object
     */
    var $db = null;

    /**
     * query counter
     * @var integer
     * @access private
     */
    var $_queries = 0;

    // }}}
    // {{{ init

    /**
     * Initialize the container
     *
     * @param  string Connection data or MDB2 object
     * @return boolean|PEAR_Error object if something went wrong
     */
    function init(&$db)
    {
        $this->_setDefaultOptions();
        if (PEAR::isError($err = $this->_connect($db))) {
            return $err;
        }
        return true;
    }

    // }}}
    // {{{ _connect()

    /**
     * Connect to database by using the given DSN string
     *
     * @access private
     * @param  mixed DSN string | array | mdb2 object
     * @return mixed  Object on error, otherwise bool
     */
    function _connect(&$db)
    {
        if (is_object($db) && is_a($db, 'MDB2_Driver_Common')) {
            $this->db = &$db;
        } elseif (is_string($db) || is_array($db)) {
            require_once 'MDB2.php';
            $this->db =& MDB2::connect($db);
        } elseif (is_object($db) && MDB2::isError($db)) {
            return PEAR::raiseError($db->getMessage(), $db->code);
        } else {
            return PEAR::raiseError('The given dsn was not valid in file '
                                    . __FILE__ . ' at line ' . __LINE__,
                                    TRANSLATION2_ERROR_CANNOT_CONNECT,
                                    PEAR_ERROR_RETURN);
        }

        if (MDB2::isError($this->db) || PEAR::isError($this->db)) {
            return PEAR::raiseError($this->db->getMessage(), $this->db->code);
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
        $this->options['langs_avail_table'] = 'langs';
        $this->options['lang_id_col']       = 'id';
        $this->options['lang_name_col']     = 'name';
        $this->options['lang_meta_col']     = 'meta';
        $this->options['lang_errmsg_col']   = 'error_text';

        $this->options['strings_default_table'] = 'i18n';
        $this->options['strings_tables']        = array(); // 'lang_id' => 'table_name'
        $this->options['string_id_col']         = 'id';
        $this->options['string_page_id_col']    = 'page_id';
        $this->options['string_text_col']       = '%s'; // col_name if one table per lang is used,
                                                        // or a pattern (i.e. "tr_%s" => "tr_EN_US")
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     */
    function fetchLangs()
    {
        $query = sprintf('SELECT %s AS id, %s AS name, %s AS meta, %s AS error_text FROM %s',
                        $this->options['lang_id_col'],
                        $this->options['lang_name_col'],
                        $this->options['lang_meta_col'],
                        $this->options['lang_errmsg_col'],
                        $this->options['langs_avail_table']);

        ++$this->_queries;
        $res = $this->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            return $res;
        }
        foreach ($res as $row) {
            $this->langs[$row['id']] = $row;
        }
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
    function &getPage($pageID = null, $langID = null)
    {
        $langID = is_null($langID) ? $this->currentLang['id'] : $langID;
        $lang_col = $this->_getLangCol($langID);
        $table = $this->_getLangTable($langID);
        
        $query = sprintf('SELECT %s, %s FROM %s WHERE %s ',
                         $this->options['string_id_col'],
                         $lang_col,
                         $table,
                         $this->options['string_page_id_col']);

        if (is_null($pageID)) {
            $query .= 'IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID, 'text');
        }

        ++$this->_queries;
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return $res;
        }

        $strings = array();
        while (list($key, $value) = $res->fetchRow()) {
            $strings[$key] = $value;
        }
        $res->free();
        return $strings;
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
        $langID = is_null($langID) ? $this->currentLang['id'] : $langID;
        $lang_col = $this->_getLangCol($langID);
        $table = $this->_getLangTable($langID);

        $query = sprintf('SELECT %s FROM %s WHERE %s.%s = %s AND %s',
                         $lang_col,
                         $table,
                         $table,
                         $this->options['string_id_col'],
                         $this->db->quote($stringID, 'text'),
                         $this->options['string_page_id_col']
                         );

        if (is_null($pageID)) {
            $query .= ' IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID, 'text');
        }

        ++$this->_queries;
        return $this->db->queryOne($query);
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
        $lang_col = $this->_getLangCol($this->currentLang['id']);
        $table = $this->_getLangTable($this->currentLang['id']);
        $query = sprintf('SELECT %s FROM %s WHERE %s = %s AND %s',
                         $this->options['string_id_col'],
                         $table,
                         $lang_col,
                         $this->db->quote($string, 'text'),
                         $this->options['string_page_id_col']
                         );
        if (is_null($pageID)) {
            $query .= ' IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID, 'text');
        }
        ++$this->_queries;
        return $this->db->queryOne($query);
    }

    // }}}
    // {{{ _getLangTable()

    /**
     * Get the table a language is stored in
     *
     * @param string $langID Language
     * @return string table $langID is stored in
     * @access private
     * @author Ian Eure
     */
    function _getLangTable($langID)
    {
        static $tables;
        if (!isset($tables[$langID])) {
            if (isset($this->options['strings_tables'][$langID])) {
                $tables[$langID] = $this->options['strings_tables'][$langID];
            } else {
                $tables[$langID] = $this->options['strings_default_table'];
            }
        }
        return $tables[$langID];
    }

    // }}}
    // {{{ _getLangCol()

    /**
     * Get the column a language's string is stored in
     *
     * @param string $langID Language
     * @return string column $langID is stored in
     * @access private
     * @author Ian Eure
     */
    function _getLangCol($langID)
    {
        static $cols;
        if (!isset($cols[$langID])) {
            if (isset($this->options['string_text_col']) &&
                !empty($this->options['string_text_col'])) {
                $cols[$langID] = str_replace('%s', $langID, $this->options['string_text_col']);
            } else {
                $cols[$langID] = $langID;
            }
        }
        return $cols[$langID];
    }

    // }}}
}
?>