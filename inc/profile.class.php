<?php

/**
 * -------------------------------------------------------------------------
 * Borgbase plugin for GLPI
 * Copyright (C) 2022-2024 by the TICgal Team.
 * https://www.tic.gal/
 * -------------------------------------------------------------------------
 * LICENSE
 * This file is part of the Borgbase plugin.
 * Borgbase plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * Borgbase plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Borgbase. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 * @package  Borgbase
 * @author    the TICgal team
 * @copyright Copyright (c) 2022-2024 TICgal team
 * @license   AGPL License 3.0 or (at your option) any later version
 * http://www.gnu.org/licenses/agpl-3.0-standalone.html
 * @link      https://www.tic.gal/
 * @since     2022
 * ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginBorgbaseProfile extends CommonDBTM
{
    public static $rightname = 'profile';

    /**
     * getTypeName
     *
     * @param  mixed $nb
     * @return string
     */
    public static function getTypeName($nb = 0): string
    {
        return "Borgbase";
    }

    /**
     * getTabNameForItem
     *
     * @param  CommonGLPI $item
     * @param  mixed $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && $item->getField('id')) {
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    /**
     * displayTabContentForItem
     *
     * @param  CommonGLPI $item
     * @param  mixed $tabnum
     * @param  mixed $withtemplate
     * @return boolean
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if ($item instanceof Profile && $item->getField('id')) {
            return self::showForProfile($item->getID());
        }

        return true;
    }

    /**
     * getAllRights
     *
     * @param  mixed $all
     * @return array
     */
    static function getAllRights($all = false): array
    {
        $rights = array(
            array(
                'itemtype' => PluginBorgbaseBorgbase::class,
                'label' => PluginBorgbaseBorgbase::getTypeName(),
                'field' => PluginBorgbaseBorgbase::getIndexName()
            )
        );

        return $rights;
    }

    /**
     * showForProfile
     *
     * @param  mixed $profiles_id
     * @return boolean
     */
    public static function showForProfile($profiles_id = 0): bool
    {
        $canupdate = self::canUpdate();
        $profile = new Profile();
        $profile->getFromDB($profiles_id);
        echo "<div class='firstbloc'>";
        echo "<form method='post' action='" . $profile->getFormURL() . "'>";

        $rights = self::getAllRights();
        $profile->displayRightsChoiceMatrix(
            $rights,
            array(
                'canedit' => $canupdate,
                'title' => self::getTypeName(),
            )
        );

        if ($canupdate) {
            echo "<div class='center'>";
            echo Html::hidden('id', array('value' => $profiles_id));
            echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
            echo "</div>\n";
            Html::closeForm();

            echo "</div>";
        }

        return true;
    }

    /**
     * install
     *
     * @return void
     */
    public static function install(): void
    {
        global $DB;

        $query = "SELECT id FROM glpi_profilerights WHERE name = '" . PluginBorgbaseBorgbase::getIndexName() . "'";
        $numRights = sizeof($DB->request($query));
        if ($numRights == 0) {
            foreach (PluginBorgbaseProfile::getAllRights() as $right) {
                ProfileRight::addProfileRights([$right['field']]);
            }
        }
    }

    /**
     * uninstall
     *
     * @return void
     */
    public static function uninstall(): void
    {
        foreach (PluginBorgbaseProfile::getAllRights() as $right) {
            //ProfileRight::deleteProfileRights([$right['field']]);
        }
    }
}
