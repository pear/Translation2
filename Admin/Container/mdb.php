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
 * require Translation2_Container_mdb class
 */
require_once 'Translation2/Container/mdb.php';

/**
 * Storage driver for storing/fetching data to/from a database
 *
 * This storage driver can use all databases which are supported
 * by the PEAR MDB abstraction layer to fetch data.
 *
 * @package  Translation2
 * @version  $Revision$
 */
class Translation2_Admin_Container_mdb extends Translation2_Container_mdb
{

    // {{{ class vars


    // }}}
    // {{{ createNewLang()

    /**
     * Creates a new table to store the strings in this language.
     * If the table is shared with other langs, it is ALTERed to
     * hold strings in this lang too.
     *
     * @param array $langData
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langData)
    {
        $tables = $this->db->listTables();
        if (PEAR::isError($tables)) {
            return $tables;
        }

        $lang_col = $this->_getLangCol($langData['lang_id']);

        if (in_array($langData['table_name'], $tables)) {
            //table exists
            $query = sprintf('ALTER TABLE %s ADD COLUMN %s TEXT',
                            $langData['table_name'],
                            $lang_col
            );
            ++$this->_queries;
            return $this->db->query($query);
        }

        //table does not exist
        $queries = array();
        $queries[] = sprintf('CREATE TABLE %s ( '
                             .'%s VARCHAR(50) default NULL, '
                             .'%s TEXT NOT NULL, '
                             .'%s TEXT )',
                             $langData['table_name'],
                             $this->options['string_page_id_col'],
                             $this->options['string_id_col'],
                             $lang_col
        );
        $queries[] = sprintf('CREATE UNIQUE INDEX %s_index ON %s (%s)',
                             $langData['table_name'],
                             $this->options['string_id_col'],
                             $langData['table_name'],
                             $this->options['string_id_col']
        );
        $queries[] = sprintf('CREATE INDEX %s_%s_index ON %s (%s)',
                             $langData['table_name'],
                             $this->options['string_page_id_col'],
                             $langData['table_name'],
                             $this->options['string_page_id_col']
        );
        $queries[] = sprintf('CREATE INDEX %s_%s_index ON %s (%s)',
                             $langData['table_name'],
                             $this->options['string_id_col'],
                             $langData['table_name'],
                             $this->options['string_id_col']
        );
        foreach($queries as $query) {
            ++$this->_queries;
            $res = $this->db->query($query);
            if ($res == false) {
                return $res;
            }
        }
        return true;
    }

    // }}}
    // {{{ addLangToAvailList()

    /**
     * Creates a new entry in the langsAvail table.
     * If the table doesn't exist yet, it is created.
     *
     * @param array $langData array('lang_id'    => 'en',
     *                              'table_name' => 'i18n',
     *                              'name'       => 'english',
     *                              'meta'       => 'some meta info',
     *                              'error_text' => 'not available');
     * @return mixed true on success, PEAR_Error on failure
     */
    function addLangToAvailList($langData)
    {
        $tables = $this->db->listTables();
        if (PEAR::isError($tables)) {
            return $tables;
        }

        if (!in_array($this->options['langs_avail_table'], $tables)) {
            $queries = array();
            $queries[] = sprintf('CREATE TABLE %s ('
                                .'%s VARCHAR(16), '
                                .'%s VARCHAR(200), '
                                .'%s TEXT, '
                                .'%s VARCHAR(250) )',
                                $this->options['langs_avail_table'],
                                $this->options['lang_id_col'],
                                $this->options['lang_name_col'],
                                $this->options['lang_meta_col'],
                                $this->options['lang_errmsg_col']
            );
            $queries[] = sprintf('CREATE UNIQUE INDEX %s_%s_index ON %s (%s)',
                                $this->options['langs_avail_table'],
                                $this->options['lang_id_col'],
                                $this->options['langs_avail_table'],
                                $this->options['lang_id_col']
            );

            foreach ($queries as $query) {
                ++$this->_queries;
                $res = $this->db->query($query);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }

        $query = sprintf('INSERT INTO %s (%s, %s, %s, %s) VALUES (%s, %s, %s, %s)',
	                $this->options['langs_avail_table'],
                    $this->options['lang_id_col'],
                    $this->options['lang_name_col'],
                    $this->options['lang_meta_col'],
                    $this->options['lang_errmsg_col'],
                    $this->db->getTextValue($langData['lang_id']),
                    $this->db->getTextValue($langData['name']),
                    $this->db->getTextValue($langData['meta']),
                    $this->db->getTextValue($langData['error_text'])
        );

        ++$this->_queries;
        $success = $this->db->query($query);
        $this->options['strings_tables'][$langData['lang_id']] = $langData['table_name'];
        return $success;
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

        if (!count($langs)) {
            //return error: no valid lang provided
            return true;
        }

        // Langs may be in different tables - we need to split up queries along
        // table lines, so we can keep DB traffic to a minimum.

        $stringID = $this->db->getTextValue($stringID);
        $pageID = is_null($pageID) ? 'NULL' : $this->db->getTextValue($pageID);
        // Loop over the tables we need to insert into.
        foreach ($this->_tableLangs($langs) as $table => $tableLangs) {
            $tableCols = $this->_getLangCols($tableLangs);
            $langData = array();
            foreach ($tableLangs as $lang) {
                $langData[$lang] =& $this->db->getTextValue($stringArray[$lang]);
            }

            $query = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (%s, %s, %s)',
                             $table,
                             $this->options['string_id_col'],
                             $this->options['string_page_id_col'],
                             implode(', ', $tableCols),
                             $stringID,
                             $pageID,
                             implode(', ', $langData)
            );
            ++$this->_queries;
            $res = $this->db->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
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
        $langs = array_intersect(
            array_keys($stringArray),
            $this->getLangs('ids')
        );

        if (!count($langs)) {
            //return error: no valid lang provided
            return true;
        }

        $stringID = $this->db->getTextValue($stringID);
        $pageID = is_null($pageID) ? ' IS NULL' : ' = ' . $this->db->getTextValue($pageID);
        foreach ($this->_tableLangs($langs) as $table => $tableLangs) {
            $tableCols = $this->_getLangCols($tableLangs);
            $langData = array();

            unset($langSet);
            foreach ($tableLangs as $lang) {
                $langSet[] = $tableCols[$lang] . ' = ' .
                             $this->db->getTextValue($stringArray[$lang]);
            }

            $query = sprintf('UPDATE %s SET %s WHERE %s = %s AND %s %s',
                             $table,
                             implode(', ', $langSet),
                             $this->options['string_id_col'],
                             $stringID,
                             $this->options['string_page_id_col'],
                             $pageID
            );

            ++$this->_queries;
            $res = $this->db->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return true;
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
        $tables = array_unique($this->_getLangTables());

        $stringID = $this->db->getTextValue($stringID);
        // get the tables and skip the non existent ones
        $dbTables = $this->db->listTables();
        foreach ($tables as $table) {
            if (!in_array($table, $dbTables)) {
                continue;
            }
            $query = sprintf('DELETE FROM %s WHERE %s = %s AND %s',
                             $table,
                             $this->options['string_id_col'],
                             $stringID,
                             $this->options['string_page_id_col']
            );
            if (is_null($pageID)) {
                $query .= ' IS NULL';
            } else {
                $query .= ' = ' . $this->db->getTextValue($pageID);
            }

            ++$this->_queries;
            $res = $this->db->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
    }

    // }}}
    // {{{ _tableLangs()

    /**
     * Get table -> language mapping
     *
     * The key of the array is the table that a language is stored in;
     * the value is an /array/ of languages stored in that table.
     *
     * @param   array  $langs  Languages to get mapping for
     * @return  array  Table -> language mapping
     * @access  private
     * @see     Translation2_Container_DB::_getLangTable()
     * @author  Ian Eure
     */
    function &_tableLangs($langs)
    {
        $tables = array();
        foreach ($langs as $lang) {
            $table = $this->_getLangTable($lang);
            $tables[$table][] = $lang;
        }
        return $tables;
    }

    // }}}
    // {{{ _getLangTables()

    /**
     * Get tables for languages
     *
     * This is like _getLangTable(), but it returns an array of the tables for
     * multiple languages.
     *
     * @param   array    $langs  Languages to get tables for
     * @return  array
     * @access  private
     * @author  Ian Eure
     */
    function &_getLangTables($langs = null)
    {
        $tables = array();
        $langs = !is_array($langs) ? $this->getLangs('ids') : $langs;
        foreach ($langs as $lang) {
            $tables[] = $this->_getLangTable($lang);
        }
        return $tables;
    }

    // }}}
    // {{{ _getLangCols()

    /**
     * Get table columns strings are stored in
     *
     * This is like _getLangCol(), except it returns an array which contains
     * the mapping for multiple languages.
     *
     * @param   array  $langs  Languages to get mapping for
     * @return  array  Language -> column mapping
     * @access  private
     * @see     Translation2_Container_DB::_getLangCol()
     * @author  Ian Eure
     */
    function &_getLangCols($langs)
    {
        $cols = array();
        foreach ($langs as $lang) {
            $cols[$lang] = $this->_getLangCol($lang);
        }
        return $cols;
    }

    // }}}
}
?>