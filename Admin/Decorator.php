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
 * Decorates a Translation2_Admin class.
 * Create a subclass of this class for your own "decoration".
 * @abstract
 * @package Translation2
 */
class Translation2_Admin_Decorator extends Translation2_Decorator
{
    function createNewLang($langData)
    {
        return $this->translation2->createNewLang($langData);
    }

    function removeLang($langID = null, $force = false)
    {
        return $this->translation2->removeLang($langID, $force);
    }

    function add($stringID, $pageID = null, $stringArray)
    {
        return $this->translation2->add($stringID, $pageID, $stringArray);
    }

    function update($stringID, $pageID = null, $stringArray)
    {
        return $this->translation2->update($stringID, $pageID, $stringArray);
    }

    function remove($stringID, $pageID = null)
    {
        return $this->translation2->remove($stringID, $pageID);
    }

    function cleanCache()
    {
        return $this->translation2->cleanCache();
    }
}
?>