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
// | Authors: Lorenzo Alberton <l dot alberton at quipo dot it>           |
// +----------------------------------------------------------------------+
//
// $Id$
//
/**
 * @package Translation2
 * @version $Id$
 */
/**
 * Load Translation2_Decorator class
 */
require_once 'Translation2/Decorator.php';
 
/**
 * Decorates a Translation2_Admin class.
 * Create a subclass of this class for your own "decoration".
 * @abstract
 * @package Translation2
 * @todo Don't allow stacking on top of regular Decorators, since that will
 *       break.
 */
class Translation2_Admin_Decorator extends Translation2_Decorator
{
    // {{{ createNewLang()

    /**
     * Create a new language
     *
     * @see  Translation2_Admin::createNewLang()
     */
    function createNewLang($langData)
    {
        return $this->translation2->createNewLang($langData);
    }
    
    // }}}
    // {{{ removeLang()

    /**
     * Remove a language
     *
     * @see  Translation2_Admin::removeLang()
     */
    function removeLang($langID = null, $force = false)
    {
        return $this->translation2->removeLang($langID, $force);
    }

    // }}}
    // {{{ add()

    /**
     * Add a translation
     *
     * @see  Translation2_Admin::add()
     */
    function add($stringID, $pageID = null, $stringArray)
    {
        return $this->translation2->add($stringID, $pageID, $stringArray);
    }

    // }}}
    // {{{ update()

    /**
     * Update a translation
     *
     * @see  Translation2_Admin::update()
     */
    function update($stringID, $pageID = null, $stringArray)
    {
        return $this->translation2->update($stringID, $pageID, $stringArray);
    }

    // }}}
    // {{{ remove()

    /**
     * Remove a translation
     *
     * @see  Translation2_Admin::remove()
     */
    function remove($stringID, $pageID = null)
    {
        return $this->translation2->remove($stringID, $pageID);
    }

    // }}}
    // {{{ getPageNames()

    /**
     * Get a list of all the pageIDs in any table.
     *
     * @see  Translation2_Admin::getPageNames()
     */
    function getPageNames()
    {
        return $this->translation2->getPageNames();
    }

    // }}}
    // {{{ cleanCache()

    /**
     * Clean the cache
     *
     * @see  Translation2_Admin::cleanCache()
     */
    function cleanCache()
    {
        return $this->translation2->cleanCache();
    }

    // }}}
}
?>