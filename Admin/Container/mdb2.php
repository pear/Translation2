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
 * require Translation2_Container_mdb2 class
 */
require_once 'Translation2/Container/mdb2.php';

/**
 * Storage driver for storing/fetching data to/from a database
 *
 * This storage driver can use all databases which are supported
 * by the PEAR MDB2 abstraction layer to fetch data.
 *
 * @package  Translation2
 * @version  $Revision$
 */
class Translation2_Admin_Container_mdb2 extends Translation2_Container_mdb2
{

    // {{{ class vars


    // }}}
    // {{{

    /**
     * Fetch the table names from the db
     * @access private
     * @return mixed array on success, PEAR_Error on failure
     */
    function _fetchTableNames()
    {
        $this->db->loadModule('manager');
        return $this->db->manager->listTables();
    }

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
        $tables = $this->_fetchTableNames();
        if (PEAR::isError($tables)) {
            return $tables;
        }

        $lang_col = str_replace('%s', $langData['lang_id'], $this->options['string_text_col']);
        if (empty($lang_col)) {
            $lang_col = $this->currentLang['id'];
        }

        if (in_array($langData['table_name'], $tables)) {
            //table exists
            $query = sprintf('ALTER TABLE %s ADD COLUMN %s TEXT',
                            $langData['table_name'],
                            $lang_col
            );
            return $this->query($query);
        }

        //table does not exist
        $queries = array();
        $queries[] = sprintf('CREATE TABLE %s ( '
                             .'%s VARCHAR(50) default NULL, '
                             .'%s VARCHAR(50) NOT NULL, '
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
            $res = $this->query($query);
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
        $tables = $this->_fetchTableNames();
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
                $res = $this->query($query);
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
                    $this->db->quote($langData['lang_id'], 'text'),
                    $this->db->quote($langData['name'], 'text'),
                    $this->db->quote($langData['meta'], 'text'),
                    $this->db->quote($langData['error_text'], 'text')
        );

        $success = $this->query($query);
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

        $oneQuery = true;
        if ($numLangs > 1) {
            for ($i=1; $i<$numLangs; $i++) {
                if ($this->options['strings_tables'][$langs[$i]] !=
                    $this->options['strings_tables'][$langs[0]]
                ) {
                    $oneQuery = false;
                    break;
                }
            }
        }

        if ($oneQuery) {
            $fields = array(
                $this->options['string_id_col'] => array(
                    'type'   => 'text',
                    'value'  => $stringID,
                    'key'    => true
                ),
                $this->options['string_page_id_col'] => array(
                    'type'   => 'text',
                    'value'  => $pageID,
                    'null'   => (is_null($pageID) ? true : false)
                ),
            );
            foreach ($langs as $langID) {
                $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
                $fields[$lang_col] = array(
                    'type'  => 'text',
                    'value' => $stringArray[$langID],
                );
            }

            $res = $this->db->replace($this->options['strings_tables'][$langs[0]], $fields);
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            foreach ($langs as $langID) {
                $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
                if (empty($lang_col)) {
                    $lang_col = $langID;
                }

                $fields = array(
                    $this->options['string_id_col'] => array(
                        'type'   => 'text',
                        'value'  => $stringID,
                        'key'    => true
                    ),
                    $this->options['string_page_id_col'] => array(
                        'type'   => 'text',
                        'value'  => $pageID,
                        'null'   => (is_null($pageID) ? true : false)
                    ),
                    $lang_col => array(
                        'type'   => 'text',
                        'value'  => $stringArray[$langID]
                    ),
                );
                $res = $this->db->replace($this->options['strings_tables'][$langID], $fields);
                if (PEAR::isError($res)) {
                    return $res;
                }
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
        $langs = $this->getLangs('ids');
        $tables = array();
        foreach ($langs as $langID) {
            if (isset($this->options['strings_tables'][$langID])) {
                $tables[] = $this->options['strings_tables'][$langID];
            } else {
                $tables[] = $this->options['strings_default_table'];
            }

        }
        $tables = array_unique($tables);
        //get the tables and remove the non existent ones from the list
        $dbTables = $this->_fetchTableNames();
        if (!PEAR::isError($dbTables)) {
            foreach ($tables as $k => $table) {
                if (!in_array($table, $dbTables)) {
                    unset($tables[$k]);
                }
            }
        }
        
        foreach ($tables as $table) {
            $query = 'DELETE FROM '.$table.' WHERE ';
            $where = array();
            $where[] = $this->options['string_id_col'] .'='. $this->db->quote($stringID, 'text');
            $where[] = $this->options['string_page_id_col']
                       . (empty($pageID) ? ' IS NULL' : '='. $this->db->quote($pageID, 'text'));
            $query .= implode(' AND ', $where);
            $res = $this->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
    }

    // }}}
}
?>