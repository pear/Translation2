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
 * require Translation2_Container class and PEAR::MDB2
 */
require_once 'Translation2'.DIRECTORY_SEPARATOR.'Container.php';
require_once 'MDB2.php';

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
     * Additional options for the storage container
     * @var array
     */
    var $options = array();

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
    // {{{ Constructor

    /**
     * Constructor of the container class
     *
     * Initate connection to the database via PEAR::MDB2
     *
     * @param  string Connection data or MDB2 object
     * @return object Returns an error object if something went wrong
     */
    function Translation2_Container_mdb2($dsn)
    {
        $this->_setDefaults();
        $this->options['dsn'] = $dsn;
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
    function _connect($dsn)
    {
        if (is_string($dsn) || is_array($dsn)) {
            $this->db =& MDB2::connect($dsn);
        } elseif (is_a($dsn, 'MDB2_Driver_Common')) {
            $this->db = $dsn;
        } elseif (is_object($dsn) && MDB2::isError($dsn)) {
            return PEAR::raiseError($dsn->getMessage(), $dsn->code);
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
    // {{{ _prepare()

    /**
     * Prepare database connection
     *
     * This function checks if we have already opened a connection to
     * the database. If that's not the case, a new connection is opened.
     * @access private
     * @return mixed True or a DB error object.
     */
    function _prepare()
    {
        return $this->_connect($this->options['dsn']);
    }

    // }}}
    // {{{ query()

    /**
     * Prepare query to the database
     *
     * This function checks if we have already opened a connection to
     * the database. If that's not the case, a new connection is opened.
     * After that the query is passed to the database.
     * @access private
     * @param  string Query string
     * @param  string query type (query, getOne, getRow, ...)
     * @return mixed  a MDB_result object or MDB_OK on success, a MDB
     *                or PEAR error on failure
     */
    function query($query, $queryType='query')
    {
        $err = $this->_prepare();
        if ($err !== true) {
            return $err;
        }
        ++$this->_queries;
        //echo '<div style="background-color: yellow; border: 1px solid red">['.$this->_queries.'] '.$query .'</div>';
        return $this->db->$queryType($query);
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
        $this->options['langs_avail_table'] = 'langs';
        $this->options['lang_id_col']       = 'ID';
        $this->options['lang_name_col']     = 'name';
        $this->options['lang_meta_col']     = 'meta';
        $this->options['lang_errmsg_col']   = 'error_text';

        $this->options['strings_tables']     = array(); // 'lang_id' => 'table_name'
        $this->options['string_id_col']      = 'ID';
        $this->options['string_page_id_col'] = 'page_id';
        $this->options['string_text_col']    = '%s'; // col_name if one table per lang is used,
                                                     // or a pattern (i.e. "tr_%s" => "tr_EN_US")
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     *
     * NB: table names will be customizable via an option...
     */
    function fetchLangs()
    {
        $query = sprintf('SELECT %s, %s, %s, %s FROM %s',
                        $this->options['lang_id_col'],
                        $this->options['lang_name_col'],
                        $this->options['lang_meta_col'],
                        $this->options['lang_errmsg_col'],
                        $this->options['langs_avail_table']);

        $res = $this->query($query);
        if (PEAR::isError($res)) {
            return $res;
        }
        $langs = array();
        $numrows = $res->numRows();
        for ($i=0; $i<$numrows; $i++) {
            $tmp = array();
            list($tmp['id'],
                 $tmp['name'],
                 $tmp['meta'],
                 $tmp['error_text']
            ) = $res->fetchRow();
            $langs[$tmp['id']] = $tmp;
        }
        $this->langs = $langs;
        $res->free();
    }

    // }}}
    // {{{ getPage()

    /**
     * Returns an array of the strings in the selected page
     *
     * This implementation can be easily changed to allow
     * a 2nd parameter, $langID, in case the RFC.3.a option
     * is the one chosen.
     *
     * NB: table names will be customizable via an option...
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    function getPage($pageID=null, $langID=null)
    {
        if (is_null($langID)) {
            $langID = $this->currentLang['id'];
        }
        $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
        if (empty($lang_col)) {
            $lang_col = $langID;
        }
        $query = sprintf('SELECT %s, %s FROM %s',
                         $this->options['string_id_col'],
                         $lang_col,
                         $this->options['strings_tables'][$langID]);
        $where = array();
        if (!empty($pageID)) {
            $where[] = $this->options['strings_tables'][$langID]. '.' .
                       $this->options['string_page_id_col']. '=' . $this->db->quote($pageID, 'text');
        } elseif (is_null($pageID)) {
            $where[] = $this->options['strings_tables'][$langID]. '.' .
                       $this->options['string_page_id_col']. ' IS NULL';
        } else {
            $where[] = $this->options['strings_tables'][$langID]. '.' .
                       $this->options['string_page_id_col']. '=""';
        }
        $query .= ' WHERE ' .implode(' AND ', $where);
        $res = $this->query($query);
        if (PEAR::isError($res)) {
            return $res;
        }
        $strings = array();
        $numrows = $res->numRows();
        for ($i=0; $i<$numrows; $i++) {
            list($key, $value) = $res->fetchRow();
            $strings[$key] = $value;
        }
        $res->free();
        return $strings;
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
        $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
        if (empty($lang_col)) {
            $lang_col = $this->currentLang['id'];
        }
        if (is_null($langID)) {
            $langID = $this->currentLang['id'];
        }
        $query = sprintf('SELECT %s FROM %s',
                         $lang_col,
                         $this->options['strings_tables'][$langID]);
        $where = array();
        if (!empty($pageID)) {
            $where[] = $this->options['strings_tables'][$langID]. '.' .
                       $this->options['string_page_id_col']. '='. $this->db->quote($pageID, 'text');
        } elseif (is_null($pageID)) {
            $where[] = $this->options['strings_tables'][$this->currentLang['id']]. '.' .
                       $this->options['string_page_id_col']. ' IS NULL';
        } else {
            $where[] = $this->options['strings_tables'][$langID]. '.' .
                       $this->options['string_page_id_col']. '=""';
        }
        $where[] = $this->options['strings_tables'][$langID]. '.' .
                   $this->options['string_id_col'] .'='. $this->db->quote($stringID, 'text');
        $query .= ' WHERE '.implode(' AND ', $where);

        $res = $this->query($query);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (!$res->numRows()) {
            return '';
        }
        $val = $res->fetch();
        $res->free();
        return $val;
    }

    // }}}
    // {{{ getStringID()

    /**
     * Get the stringID for the given string
     * @param string $stringID
     * @param string $pageID
     * @return string
     */
    function getStringID($string, $pageID=null)
    {
        $lang_col = str_replace('%s', $this->currentLang['id'], $this->options['string_text_col']);
        if (empty($lang_col)) {
            $lang_col = $this->currentLang['id'];
        }
        $query = sprintf('SELECT %s FROM %s WHERE %s=%s',
                         $this->options['string_id_col'],
                         $this->options['strings_tables'][$this->currentLang['id']],
                         $lang_col,
                         $this->db->quote($string, 'text')
                         );
        if (!empty($pageID)) {
            $query .= ' AND '.$this->options['strings_tables'][$this->currentLang['id']]. '.'
                    . $this->options['string_page_id_col']. '='. $this->db->quote($pageID, 'text');
        } elseif (is_null($pageID)) {
            $query .= ' AND '.$this->options['strings_tables'][$this->currentLang['id']]. '.'
                    . $this->options['string_page_id_col']. ' IS NULL';
        } else {
            $query .= ' AND '.$this->options['strings_tables'][$this->currentLang['id']]. '.'
                    . $this->options['string_page_id_col']. '=""';
        }
        return $this->query($query, 'queryOne');
    }

    // }}}
}
?>