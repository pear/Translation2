<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Translation2 PEAR::DB_DataObject container
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
 * @author     Alan Knowles <alan@akbkhome.com>
 * @copyright  2004-2005 Alan Knowles
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */

/**
 * require Translation2_Container class and DB_DataObjects
 */
require_once 'Translation2/Container.php';
require_once 'DB/DataObject.php';

/**
 * Simple storage driver for fetching data from a db with DB_DataObject
 *
 * Database Structure:
 *
 *  // meta data etc. not supported yet...
 *
 *  create table translations (
 *     id int(11) auto_increment not null primary key,
 *     string_id int(11),
 *     page varchar(128),
 *     lang varchar(10),
 *     translation text
 *     );
 * alter table translations add index page (page);
 * alter table translations add index lang (lang);
 * alter table translations add index string_id (string_id);
 *
 * - then just run the dataobjects createtables script.
 *
 *
 * This storage driver can use all databases which are supported
 * by the PEAR DB abstraction layer to fetch data.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Alan Knowles <alan@akbkhome.com>
 * @copyright  2004-2005 Alan Knowles
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Container_dataobjectsimple extends Translation2_Container
{
    // {{{ init

    /**
     * Initialize the container
     *
     * @param  string table name
     * @return boolean true
     */
    function init($table = null)
    {
        $this->_setDefaultOptions();
        if (!empty($table)) {
            $this->options['table'] = $table;
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
        $this->options['table'] = 'translations';
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     */
    function fetchLangs()
    {
        $do = DB_DataObject::factory($this->options['table']);
        $do->selectAdd();
        $do->selectAdd('distinct lang');
        $do->find();

        $ret = array();
        while ($do->fetch()) {
            $l = $do->lang;
            $ret[$l] = array(
                'id'         => $l,
                'name'       => $l,
                'meta'       => '',
                'error_text' => '',
            );
        }
        $this->langs =  $ret;
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
        $langID = $this->_getLangID($langID);

        $do = DB_DataObject::factory($this->options['table']);
        $do->lang = $langID;
        $do->page = $pageID;

        $do->find();
        $strings = array();
        while ($do->fetch()) {
            $strings[$do->string_id] = $do->translation;
        }

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
    function getOne($string, $pageID = null, $langID = null)
    {
        $langID = $langID ? $langID : (isset($this->currentLang['id']) ? $this->currentLang['id'] : '-');
        // get the string id
        $do = DB_DataObject::factory($this->options['table']);
        $do->lang = '-';
        $do->page = $pageID;
        $do->translation = $string;
        // we dont have the base language translation..
        if (!$do->find(true)) {
            return '';
        }
        $stringID = $do->string_id;

        $do = DB_DataObject::factory($this->options['table']);
        $do->lang = $langID;
        $do->page = $pageID;
        $do->string_id = $stringID;
        //print_r($do);
        $do->selectAdd();
        $do->selectAdd('translation');
        if (!$do->find(true)) {
            return '';
        }
        return $do->translation;

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
        // get the english version...

        $do = DB_DataObject::factory($this->options['table']);
        $do->lang = $this->currentLang['id'];
        $do->page = $pageID;
        $do->translation = $string;
        if ($do->find(true)) {
            return '';
        }
        return $do->string_id;
    }

    // }}}
}
?>
