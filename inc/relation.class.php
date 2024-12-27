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

class PluginBorgbaseRelation extends CommonDBRelation
{
    /**
     * getTypeName
     *
     * @param  mixed $nb
     * @return string
     */
    public static function getTypeName($nb = 0): string
    {
        return 'Borgbase Relation';
    }

    /**
     * install
     *
     * @param  Migration $migration
     * @return boolean
     */
    public static function install(Migration $migration): bool
    {
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemtype('PluginBorgbaseRelation');
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $query = "CREATE TABLE `$table` (
                `id`         				    INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                `items_id`                      INT {$default_key_sign} NOT NULL DEFAULT '0',
                `itemtype`                      VARCHAR(255) DEFAULT NULL,
                `plugin_borgbase_borgbases_id`  INT {$default_key_sign} NOT NULL DEFAULT '0',
                PRIMARY KEY  (`id`),
                KEY `items_id` (`items_id`),
                KEY `itemtype` (`itemtype`,`items_id`),
                KEY `plugin_borgbase_borgbases_id` (`plugin_borgbase_borgbases_id`)
            ) ENGINE=InnoDB
                DEFAULT CHARSET={$default_charset}
                COLLATE={$default_collation}";
            $DB->request($query) or die($DB->error());
        }

        return true;
    }

    /**
     * uninstall
     *
     * @param  Migration $migration
     * @return boolean
     */
    public static function uninstall(Migration $migration): bool
    {
        global $DB;
        $table = self::getTable();
        //$migration->displayMessage('Uninstalling ' . $table);

        //$DB->queryOrDie("DROP TABLE `$table`", $DB->error());

        return true;
    }
}
