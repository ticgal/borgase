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
    die("Sorry. You can't access directly to this file");
}

class PluginBorgbaseProvider extends CommonDBTM
{
    public static function usageHistory($params = [])
    {
        $data = [];

        $borgbase = new PluginBorgbaseBorgbase;
        $usages = $borgbase->getCurrentUsage();

        // New rows last
        krsort($usages);

        foreach ($usages as $usage) {
            $dateParts = explode("-", $usage['date']);
            $date = $dateParts[2] . '/' . $dateParts[1]; // day/month

            $use = number_format($usage['usedGb'], 2, '.');

            $data[] = [
                'number' => $use,
                'label' => $date,
            ];
        }

        if (count($data) === 0) {
            $data = [
                'nodata' => true
            ];
        }

        $provide = [
            'data' => $data,
            'label' => 'Borgbase - ' . __('Total Usage History') . ' (GB)',
            'icon' => PluginBorgbaseBorgbase::getIcon()
        ];

        return $provide;
    }

    public static function usageQuotaPer($params = [])
    {
        $data = [];

        $borgbase = new PluginBorgbaseBorgbase;
        $usages = $borgbase->getCurrentUsage();

        $currentUsage = $usages[0]['usedGb'];
        $limitUsage = $usages[0]['plan']['includedSize'] / 1000; //GB

        $calc = ($currentUsage * 100) / $limitUsage;
        $percentage = number_format($calc, 2);

        $data[] = [
            'number' => $percentage,
            'label' => __('Storage usage', 'borgbase'),
        ];
        $data[] = [
            'number' => 100 - $percentage,
            'label' => __('Free storage', 'borgbase'),
        ];

        if (count($data) === 0) {
            $data = [
                'nodata' => true
            ];
        }

        $provide = [
            'data' => $data,
            'label' => 'Borgbase - ' . __('Total Usage Percentage', 'borgbase'),
            'icon' => PluginBorgbaseBorgbase::getIcon()
        ];

        return $provide;
    }

    public static function numberRepositories($params = [])
    {
        $borgbase = new PluginBorgbaseBorgbase;
        $numRepos = count($borgbase->getRepoList());

        $provide = [
            'number' => $numRepos,
            'label' => 'Borgbase - ' . __('Number of Repositories', 'borgbase'),
            'icon' => PluginBorgbaseBorgbase::getIcon()
        ];

        return $provide;
    }

    public static function currentUse($params = [])
    {
        $borgbase = new PluginBorgbaseBorgbase;
        $currentUse = $borgbase->getCurrentUsage()[0]['usedGb'];

        $provide = [
            'number' => $currentUse,
            'label' => 'Borgbase - ' . __('Current Use', 'borgbase') . ' (GB)',
            'icon' => PluginBorgbaseBorgbase::getIcon()
        ];

        return $provide;
    }

    public static function numberOfLinkedRepositories($params = [])
    {
        global $DB;

        $sub_query = new \QuerySubQuery([
            'SELECT' => 'items_id',
            'FROM'   => PluginBorgbaseRelation::getTable()
        ]);

        $iterator = $DB->request([
            'SELECT' => ['COUNT DISTINCT' => 'id as count'],
            'FROM' => 'glpi_computers',
            'WHERE' => [
                'id' => $sub_query
            ]
        ]);
        
        $result = $iterator->current();
        $linkedRepositories = $result['count'];

        $search_url = Computer::getSearchURL();
        $search_criteria['criteria'][] = [
            'link'       => '',
            'field'      => PluginBorgbaseBorgbase::$sopt,
            'searchtype' => 'notcontains',
            'value'      => 'null'
        ];
        
        $url = $search_url . (str_contains($search_url, '?') ? '&' : '?') . Toolbox::append_params([
            $search_criteria,
            'reset' => 'reset',
        ]);
        

        $provide = [
            'number' => $linkedRepositories,
            'url' => $url,
            'label' => 'Borgbase - ' . __('Number of Computers with a Repo Linked', 'borgbase'),
            'icon' => PluginBorgbaseBorgbase::getIcon()
        ];

        return $provide;
    }
}