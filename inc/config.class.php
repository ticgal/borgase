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

use Glpi\Application\View\TemplateRenderer;

class PluginBorgbaseConfig extends CommonDBTM
{
    static function canCreate()
    {
        return Session::haveRight('config', UPDATE);
    }

    static function canView()
    {
        return Session::haveRight('config', READ);
    }

    static function canUpdate()
    {
        return Session::haveRight('config', UPDATE);
    }

    /**
     * getTabNameForItem
     *
     * @param  CommonGLPI $item
     * @param  mixed $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            if ($item->getType() == 'Config') {
                return 'Borgbase';
            }
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
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Config') {
            $config = new self();
            $config->showFormExample();
            return true;
        }
        return false;
    }

    /**
     * showFormExample
     *
     */
    public function showFormExample()
    {
        if (!Session::haveRight("config", UPDATE)) {
            return false;
        }

        $config = new PluginBorgbaseConfig;
        $config->getFromDB(1);

        $connect = false;
        $msg = "<span class='text-muted'><i class='me-2 fa fa-check'></i>" . __('Established connection', 'borgbase') . "</span>";
        $options = [
            'full_width' => true
        ];

        $labels = [
            'server' => __('Server endpoint', 'borgbase'),
            'apikey' => __('Authentication key', 'borgbase'),
            'match' => __('Computer name matches exactly with repository name', 'borgbase'),
            'reload' => __('Link repositories', 'borgbase')
        ];
        
        $borgbase = new PluginBorgbaseBorgbase;
        $req = $borgbase->request('{isAuthenticated}');
        if (str_contains($req, 'true')) {
            $connect = true;
        }

        $templatePath = "@borgbase/config.html.twig";
        TemplateRenderer::getInstance()->display(
            $templatePath,
            [
                'item' => $config,
                'connection' => $connect,
                'labels' => $labels,
                'msg' => $msg,
                'options' => $options,
            ]
        );
    }

    /**
     * Summary of linkAvailableRepos
     * @return int
     */
    public function linkAvailableRepos()
    {
        global $DB;
        $borgbase = new PluginBorgbaseBorgbase;
        $repoList = $borgbase->getRepoList();
        $linkedRepos = 0;

        foreach ($repoList as $repo) {
            $req = $DB->request([
                'SELECT' => 'id',
                'FROM' => 'glpi_computers',
                'WHERE' => [
                    'name' => $repo['name']
                ]
            ]);

            $computer = $req->current();
            if ($computer) {
                $linked = $borgbase->linkRepo($repo, $computer['id']);
                if($linked){
                    $linkedRepos++;
                }
            }
        }

        return $linkedRepos;
    }

    /**
     * install
     *
     * @param  Migration $migration
     * @return boolean
     */
    public static function install(Migration $migration)
    {
        global $DB;
        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = "CREATE TABLE IF NOT EXISTS $table (
				`id` int {$default_key_sign} NOT NULL auto_increment,
				`server` VARCHAR(512) NOT NULL DEFAULT '',
				`apikey` VARCHAR(640) NOT NULL DEFAULT '',
				`match` tinyint(1) NOT NULL DEFAULT '0',
				`debug` tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			)ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->query($query) or die($DB->error());

            // Default config
            $DB->insert(
                $table,
                [
                    'server' => 'https://api.borgbase.com/graphql',
                    'apikey' => '',
                    'match' => '1',
                    'debug' => '0',
                ]
            );
        } else {
            //0.1.5
            $DB->queryOrDie("ALTER TABLE `${table}` ENGINE=InnoDB");
            $migration->executeMigration();
        }

        return true;
    }

    /**
     * uninstall
     *
     * @param  Migration $migration
     * @return boolean
     */
    public static function uninstall(Migration $migration)
    {
        global $DB;
        $table = self::getTable();
        //$migration->displayMessage('Uninstalling ' . $table);

        //$DB->queryOrDie("DROP TABLE `$table`", $DB->error());

        return true;
    }
}