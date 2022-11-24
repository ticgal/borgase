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

class PluginBorgbaseBorgbase extends CommonDBTM
{
    static $rightname = 'Borgbase';
    static $itemtype_1 = 'borgbase';
    static $sopt = 1468;
    public $dohistory = true;

    /**
     * getTypeName
     *
     * @param  mixed $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return 'Borgbase';
    }

    /**
     * getIndexName
     *
     * @return string
     */
    public static function getIndexName()
    {
        return 'Borgbase';
    }

    /**
     * getIcon
     *
     * @return string
     */
    public static function getIcon()
    {
        return 'fa-solid fa-hard-drive';
    }


    // Display Tab

    /**
     * getTabNameForItem
     *
     * @param  CommonGLPI $item
     * @param  mixed $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case 'Computer':
                return 'Borgbase';
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
        switch ($item::getType()) {
            case 'Computer':
                if (Session::haveRight(self::$rightname, READ)) {
                    self::displayTab();
                }
                break;
        }
        return true;
    }

    /**
     * request
     *
     * @param  mixed $query
     * @return string
     */
    public function request($query = '')
    {
        if ($query) {
            $config = new PluginBorgbaseConfig;
            $config->getFromDB(1);
            $token = $config->fields['apikey'];

            if ($token) {
                $glpikey = new GLPIKey;

                $header = [
                    'Content-Type: application/json',
                    "Authorization: Bearer " . $glpikey->decrypt($token),
                ];
                $json = '{"query":"' . $query . '"}';
                $ch = curl_init($config->fields['server']);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                curl_close($ch);

                //if (str_contains($data, 'errors')) {
                //    echo '<td><span class="text-muted"><i class="fa-solid fa-xmark"></i> ' . __('Check API connection', 'borgbase') . '</span></td>';
                //}
                return $data;
            }
        }
        return '';
    }

    // Requests

    /**
     * getRepo
     *
     * @param  mixed $repoId
     * @return array
     */
    public function getRepo($repoId)
    {
        $query = '{ repo(repoId:\"' . $repoId . '\") {id,name,alertDays,borgVersion,region,encryption,createdAt,lastModified,compactionEnabled,compactionInterval,compactionIntervalUnit,compactionHour,compactionHourTimezone,repoPath,currentUsage}}';
        $repo = $this->request($query);
        return $this->formatRawRequest($repo);
    }
        
    /**
     * getRepoByName
     *
     * @param  string $name
     * @return array
     */
    public function getRepoByName($name)
    {
        $query = '{ repoList(name:\"' . $name . '\") {id, name,alertDays,borgVersion,encryption,region,createdAt,lastModified,compactionEnabled,compactionInterval, compactionIntervalUnit,compactionHour,compactionHourTimezone,repoPath,currentUsage}}';
        $repo = $this->request($query);
        return $this->formatRawRequest($repo);
    }

    /**
     * getRepoList
     *
     * @return array
     */
    public function getRepoList()
    {
        $query = '{ repoList {id,name,alertDays,borgVersion,region,encryption,createdAt,lastModified,compactionEnabled,compactionInterval,compactionIntervalUnit,compactionHour,compactionHourTimezone,repoPath,currentUsage}}';
        $repoList = $this->request($query);
        return $this->formatRawRequest($repoList);
    }

    /**
     * getCurrentUsage
     *
     * @return array
     */
    public function getCurrentUsage()
    {
        $query = '{ overageList {usedGb,date,plan{includedSize}}}';
        $req = $this->request($query);
        return $this->formatRawRequest($req);
    }

    /**
     * checkComputerRepo
     *
     * @param  mixed $id
     * @return array
     */
    public function checkComputerRepo($id)
    {
        // Check if exists in our database
        global $DB;

        // Request all
        $iterator = $DB->request([
            'FROM' => $this->getTable(),
            'WHERE' => [
                'computer_id' => $id
            ]
        ]);
        
        $rows = [];
        foreach($iterator as $data){
            array_push($rows, $data);
        }

        return $rows;
    }

    /**
     * checkLink
     *
     * @param  mixed $repoID
     * @return array
     */
    public function checkLink($repoID)
    {
        // Check if exists relation // computer_id
        global $DB;
        
        $iterator = $DB->request([
            'SELECT' => 'computer_id',
            'FROM' => $this->getTable(),
            'WHERE' => [
                'borg_id' => $repoID,
                'computer_id' => ['NOT LIKE', '']
            ]
        ]);
        
        $rows = [];
        foreach($iterator as $data){
            array_push($rows, $data);
        }

        return $rows;
    }

    /**
     * linkRepo
     *
     * @param  mixed $repo
     * @param  mixed $id
     * @return boolean
     */
    public function linkRepo($repo, $id)
    {
        global $DB;
        $table = $this->getTable();

        $res = $this->checkLink($repo['id']);
        if (count($res) == 0) {
            // Repository
            $DB->insert(
                $table,
                [
                    'borg_id' => $repo['id'],
                    'borg_name' => $repo['name'],
                    'computer_id' => $id,
                    'alertDays' => $repo['alertDays'],
                    'borg_version' => $repo['borgVersion'],
                    'is_encrypted' => $repo['encryption'],
                    'region' => $repo['region'],
                    'createdAt' => $repo['createdAt'],
                    'lastModified' => $repo['lastModified'],
                    'compactionInterval' => $repo['compactionInterval'],
                    'compactionIntervalUnit' => $repo['compactionIntervalUnit'],
                    'compactionHour' => $repo['compactionHour'],
                    'compactionHourTimezone' => $repo['compactionHourTimezone'],
                    'repoPath' => $repo['repoPath'],
                    'currentUsage' => $repo['currentUsage'],
                ]
            );

            // Relation
            $lastId = $DB->insertId();
            $DB->insert(
                PluginBorgbaseRelation::getTable(),
                [
                    'items_id' => $id,
                    'itemtype' => 'Computer',
                    'plugin_borgbase_borgbases_id' => $lastId
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * unlinkRepo
     *
     * @param  mixed $computerId
     * @param  mixed $repoId
     * @return void
     */
    public function unlinkRepo($computerId, $repoId)
    {
        // Check if exists in our database
        global $DB;
        $table = $this->getTable();
        $DB->delete(
            $table,
            [
                'borg_id' => $repoId
            ]
        );

        $DB->delete(
            PluginBorgbaseRelation::getTable(),
            [
                'items_id' => $computerId
            ]
        );

        return true;
    }

    /**
     * reloadRepo
     *
     * @param  mixed $repoId
     * @return boolean
     */
    public function reloadRepo($repoId)
    {
        global $DB;
        $repo = $this->getRepo($repoId);

        if ($repo) {
            $DB->update(
                $this->getTable(),
                [
                    'alertDays' => $repo['alertDays'],
                    'borg_version' => $repo['borgVersion'],
                    'is_encrypted' => $repo['encryption'],
                    'region' => $repo['region'],
                    'createdAt' => $repo['createdAt'],
                    'lastModified' => $repo['lastModified'],
                    'compactionInterval' => $repo['compactionInterval'],
                    'compactionIntervalUnit' => $repo['compactionIntervalUnit'],
                    'compactionHour' => $repo['compactionHour'],
                    'compactionHourTimezone' => $repo['compactionHourTimezone'],
                    'repoPath' => $repo['repoPath'],
                    'currentUsage' => $repo['currentUsage'],
                    'date_mod' => date('Y-m-d H:i:s'),
                ],
                [
                    'WHERE' => ['borg_id' => $repoId],
                ]
            );

            return true;
        }

        return '';
    }

    // Formating and display

    /**
     * formatRawRequest
     *
     * @param  mixed $raw
     * @return array
     */
    public function formatRawRequest($raw)
    {
        $array = json_decode($raw, true);

        // API returns always {"data":{"x"}} we only want x content
        $format = [];
        foreach ($array as $data) {
            foreach ($data as $request) {
                $format = $request;
            }
        }

        return $format;
    }
        
    /**
     * formatDate
     *
     * @param  mixed $date
     * @return string
     */
    public function formatDate($date){
        $format = 'd F Y, h:i:s A';
        $newDate = date_create($date);
        return date_format($newDate, $format);
    }
    
    /**
     * convertUsage
     *
     * @param  mixed $usage
     * @return array
     */
    public function convertUsage($usage){
        $ints = strtok($usage, '.');
        $count = strlen($ints);
        $conversedUsage = 0;
        $unit = '';

        switch ($count) {
            case $count <= 3:
                $conversedUsage = number_format($usage, 2, '.', '');
                $unit = 'MB';
                break;
            case $count <= 6:
                $conversedUsage = number_format($usage / 1000, 2, '.', '');
                $unit = 'GB';
                break;
            case $count <= 9:
                $conversedUsage = number_format($usage / 1000000, 2, '.', '');
                $unit = 'TB';
                break;
        }
        
        return ['convert' => $conversedUsage, 'unit' => $unit ];
    }

    /**
     * automaticLink
     *
     * @param  Computer $computer
     * @param  array $repoList
     * @return boolean
     */
    public function automaticLink($computer, $repoList)
    {
        $config = new PluginBorgbaseConfig;
        $config->getFromDB(1);

            $id = $computer->fields['id'];
            // Automatic insert into BD
            foreach ($repoList as $repo) {
                // Trimming to ignore white spaces
                if (strcmp(trim($computer->fields['name']), trim($repo['name'])) == 0) {
                    if ($config->fields['match']) {
                        $feedback = $this->linkRepo($repo, $id);
                        if ($feedback) {
                            // Changes
                            $changes[0] = $id;
                            $changes[1] = "";
                            $changes[2] = sprintf(__('%s, ' . __('automatically linked')), $repo['id']);

                            Log::history(
                                $id,
                                'Computer',
                                $changes,
                                'PluginBorgbaseBorgbase',
                                LOG::HISTORY_ADD_RELATION
                            );
                            HTML::redirect("computer.form.php?id=" . $id);
                            exit;
                        }
                    }
                }
            }

        return false;
    }
    
    /**
     * displayTab
     *
     * @return boolean
     */
    public static function displayTab()
    {
        if (!Session::haveRight(self::$rightname, READ)) {
            return false;
        }

        $borgbase = new self();
        $id = $_GET['id'];
        $computer = new Computer();
        $computer->getFromDB($id);
        $computerId = $computer->fields['id'];
        $computerName = $computer->fields['name'];

        $data = [];
        $elements = [];
        $options = [];

        $res = $borgbase->checkComputerRepo($id);

        if (count($res) == 0) {
            $repoListFounded = $borgbase->getRepoByName($computerName);

            $borgbase->automaticLink($computer, $repoListFounded);

            $repoList = $borgbase->getRepoList();
            foreach ($repoList as $repo) {
                $req = $borgbase->checkLink($repo['id']);
                if (count($req) == 0) {
                    $elements[$repo['id']] = $repo['name'];
                }
            }

            $templatePath = "@borgbase/dropdown.html.twig";
        } else {
            $row = $res[0];

            //Usage into KB/MG/GB
            $usage = $borgbase->convertUsage($row['currentUsage']);
            
            // Modifications
            $row['formatCreatedAt'] = $borgbase->formatDate($row['createdAt']);
            $row['formatLastModified'] = $borgbase->formatDate($row['lastModified']);
            $row['formatCompactionInterval'] = $row['compactionInterval'] . ' ' . $row['compactionIntervalUnit'];
            $row['formatCompactionHour'] = $row['compactionHour'] . ':00 (' . $row['compactionHourTimezone'] . ')';
            $row['formatCurrentUsage'] = $usage['convert'] . ' ' . $usage['unit'];
            $row['footerDateCreation'] = __('Created on', 'borgbase') . ' ' . $row['date_creation'];
            $row['footerDateMod'] = __('Last update on', 'borgbase') . ' ' . $row['date_mod'];

            $data = $row;
            $templatePath = "@borgbase/repository.html.twig";
        }
        
        $labels = [
            'notRegistered' => __('Not registered', 'borgbase'),
            'selectRepo' => __('Select a repository', 'borgbase'),
            'name' => __('Name', 'borgbase'),
            'alertDays' => __('Alert Days', 'borgbase'),
            'version' => __('Version', 'borgbase'),
            'region' => __('Region', 'borgbase'),
            'compactionInterval' => __('Compaction Interval', 'borgbase'),
            'compactionHour' => __('Compaction Hour', 'borgbase'),
            'encryption' => __('Encryption', 'borgbase'),
            'usage' => __('Current Usage', 'borgbase'),
            'createdAt' =>  __('Creation Date', 'borgbase'),
            'lastBackup' => __('Last Backup', 'borgbase'),
            'confirmDeletion' => __('Confirm the final deletion?', 'borgbase'),
            'unlink' => __('Unlink repository', 'borgbase'),
            'reload' => __('Reload information', 'borgbase')
        ];

        TemplateRenderer::getInstance()->display(
            $templatePath,
            [
                'item' => $borgbase,
                'elements' => $elements,
                'data' => $data,
                'value' => $computerId,
                'labels' => $labels,
                'params' => $options,
                'canCreate' => Session::haveRight(self::$rightname, CREATE),
                'canUpdate' => Session::haveRight(self::$rightname, UPDATE),
                'canPurge' => Session::haveRight(self::$rightname, PURGE)
            ]
        );

        return true;
    }

    /**
     * getAddSearchOptions
     *
     * @param  mixed $itemtype
     * @return array
     */
    public static function getAddSearchOptions($itemtype)
    {
        $sopt = [];

        if ($itemtype == 'Computer' && Session::haveRight(self::$rightname, READ)) {
            $sopt[self::$sopt] = [
                'table' => PluginBorgbaseBorgbase::getTable(),
                'field' => 'borg_name',
                'name' => 'Borgbase',
                'datatype' => 'text',
                'forcegroupby' => true,
                'usehaving' => true,
                'joinparams' => [
                    'beforejoin' => [
                        'table' => PluginBorgbaseRelation::getTable(),
                        'joinparams' => [
                            'jointype' => 'itemtype_item',
                        ],
                    ],
                ],
            ];
        }

        return $sopt;
    }

    /**
     * install
     *
     * @param  mixed $migration
     * @return boolean
     */
    public static function install(Migration $migration)
    {
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemtype('PluginBorgbaseBorgbase');
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $query = "CREATE TABLE `$table` (
                `id`         				INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                `borg_id` 				    VARCHAR(8) NOT NULL,
                `borg_name`     			VARCHAR(255) NOT NULL,
				`computer_id`				INT {$default_key_sign},
                `alertDays`      			INT(11),
                `borg_version`   			VARCHAR(255),
                `is_encrypted`      		VARCHAR(255),
                `region`      		        VARCHAR(255),
                `createdAt`  				VARCHAR(255),
                `lastModified`  			VARCHAR(255),
                `compactionInterval`  	    VARCHAR(255),
                `compactionIntervalUnit`	VARCHAR(255),
                `compactionHour`  		    VARCHAR(255),
                `compactionHourTimezone`    VARCHAR(255),
                `repoPath`  				VARCHAR(255),
                `currentUsage`  			VARCHAR(255) DEFAULT '0',
                `date_creation`             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`date_mod`                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB
                DEFAULT CHARSET={$default_charset}
                COLLATE={$default_collation}";
            $DB->queryOrDie($query, $DB->error());
        }

        return true;
    }

    /**
     * uninstall
     *
     * @param  mixed $migration
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