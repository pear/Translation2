<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Translation2 PEAR::DB container
 *
 * This storage driver can use all databases which are supported
 * by the PEAR::DB abstraction layer to fetch data.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Ian Eure <ieure at php dot net>
 * @copyright  2004-2005 Lorenzo Alberton, Ian Eure
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * require Translation2_Container class
 */
require_once 'Translation2/Container.php';

/**
 * Storage driver for fetching data from a database
 *
 * This storage driver can use all databases which are supported
 * by the PEAR DB abstraction layer to fetch data.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @author     Ian Eure <ieure at php dot net>
 * @copyright  2004-2005 Lorenzo Alberton, Ian Eure
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Container_db extends Translation2_Container
{
    // {{{ class vars

    /**
     * DB object
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
     * @param  mixed  string DSN or object DB instance
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
     * @param  mixed  string DSN or object DB instance
     * @return mixed  Object on error, otherwise bool
     */
    function _connect(&$db)
    {
        if (is_object($db) && is_a($db, 'DB_Common')) {
            // Passed an existing instance
            $this->db =& $db;
        } else if (is_string($db) || is_array($db)) {
            // Passed a DSN
            require_once 'DB.php';
            $this->db =& DB::connect($db);
        } else {
            // Passed something invalid
            return PEAR::raiseError('The given dsn was not valid in file '
                                    . __FILE__ . ' at line ' . __LINE__,
                                    TRANSLATION2_ERROR_CANNOT_CONNECT,
                                    PEAR_ERROR_RETURN);
        }

        if (DB::isError($this->db) || PEAR::isError($this->db)) {
            return PEAR::raiseError($this->db->getMessage(), $this->db->getCode());
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
        $this->options['lang_encoding_col'] = 'encoding';

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
        $query = sprintf('SELECT %s AS id, %s AS name, %s AS meta, %s AS error_text, %s AS encoding FROM %s',
                        $this->options['lang_id_col'],
                        $this->options['lang_name_col'],
                        $this->options['lang_meta_col'],
                        $this->options['lang_errmsg_col'],
                        $this->options['lang_encoding_col'],
                        $this->options['langs_avail_table']);

        ++$this->_queries;
        $res = $this->db->getAll($query, DB_FETCHMODE_ASSOC);
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
        $langID   = $this->_getLangID($langID);
        $lang_col = $this->_getLangCol($langID);
        $table    = $this->_getLangTable($langID);

        $query = sprintf('SELECT %s, %s FROM %s WHERE %s ',
                         $this->options['string_id_col'],
                         $lang_col,
                         $table,
                         $this->options['string_page_id_col']);

        if (is_null($pageID)) {
            $query .= 'IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID);
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
     * Get a single item from the container
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @return string
     */
    function getOne($stringID, $pageID = null, $langID = null)
    {
        $langID   = $this->_getLangID($langID);
        $lang_col = $this->_getLangCol($langID);
        $table    = $this->_getLangTable($langID);

        $query = sprintf('SELECT %s FROM %s WHERE %s = %s AND %s',
                         $lang_col,
                         $table,
                         $this->options['string_id_col'],
                         $this->db->quote($stringID),
                         $this->options['string_page_id_col']
                         );

        if (is_null($pageID)) {
            $query .= ' IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID);
        }

        ++$this->_queries;
        return $this->db->getOne($query);
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
                         $this->db->quote($string),
                         $this->options['string_page_id_col']
                         );
        if (is_null($pageID)) {
            $query .= ' IS NULL';
        } else {
            $query .= ' = ' . $this->db->quote($pageID);
        }
        ++$this->_queries;
        return $this->db->getOne($query);
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
                $tables[$langID] = str_replace('%s', $langID,
                    $this->options['strings_default_table']);
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