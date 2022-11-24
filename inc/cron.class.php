<?php
/*
 -------------------------------------------------------------------------
 Borgbase plugin for GLPI
 Copyright (C) 2021-2022 by the TICgal Team.
 https://www.tic.gal/
 -------------------------------------------------------------------------
 LICENSE
 This file is part of the Borgbase plugin.
 Borgbase plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.
 Borgbase plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Borgbase. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package  Borgbase
 @author    the TICgal team
 @copyright Copyright (c) 2021-2022 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
 http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://www.tic.gal/
 @since     2021-2022
 ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginBorgbaseCron extends CommonDBTM
{
   /**
    * cronInfo
    *
    * @param  string $name
    * @return array
    */
   static function cronInfo($name)
   {

      switch ($name) {
         case 'borgbaseUpdate':
            return ['description' => __('Update borgbase records', 'borgbase')];
      }
      return array();
   }

   /**
    * cronBorgbaseUpdate
    *
    * @param  mixed $task
    * @return boolean
    */
   static function cronBorgbaseUpdate($task = NULL)
   {
      global $DB;
      $borgbase = new PluginBorgbaseBorgbase;
      $table = PluginBorgbaseBorgbase::getTable();
      foreach ($DB->request(['SELECT' => 'borg_id', 'FROM' => $table]) as $id => $row) {
         $borgbase->reloadRepo($row['borg_id']);
      }

      return true;
   }
}