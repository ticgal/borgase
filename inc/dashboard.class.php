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

class PluginBorgbaseDashboard extends CommonDBTM
{
    public static function dashboardCards($cards): array
    {
        $cards['plugin_borgbase_usagehistory'] = [
            'widgettype'    => ['bar', 'line'],
            'label'         => __('Usage History', 'borgbase'),
            'group'         => 'Borgbase',
            'filters'       => ['dates'],
            'provider'      => 'PluginBorgbaseProvider::usageHistory'
        ];

        $cards['plugin_borgbase_usedquotapercentage'] = [
            'widgettype'    => ['donut', 'pie', 'halfdonut', 'halfpie'],
            'label'         => __('Used Quota', 'borgbase'),
            'group'         => 'Borgbase',
            'filters'       => [],
            'provider'      => 'PluginBorgbaseProvider::usageQuotaPer'
        ];

        $cards['plugin_borgbase_numberrepositories'] = [
            'widgettype'    => ['bigNumber'],
            'label'         => __('Number of Repositories', 'borgbase'),
            'group'         => 'Borgbase',
            'filters'       => [],
            'provider'      => 'PluginBorgbaseProvider::numberrepositories'
        ];

        $cards['plugin_borgbase_currentuse'] = [
            'widgettype'    => ['bigNumber'],
            'label'         => __('Current Use', 'borgbase'),
            'group'         => 'Borgbase',
            'filters'       => [],
            'provider'      => 'PluginBorgbaseProvider::currentuse'
        ];

        $cards['plugin_borgbase_numberoflinkedrepositories'] = [
            'widgettype'    => ['bigNumber'],
            'label'         => __('Number of Computers with a Repo Linked', 'borgbase'),
            'group'         => 'Borgbase',
            'filters'       => [],
            'provider'      => 'PluginBorgbaseProvider::numberoflinkedrepositories'
        ];

        return $cards;
    }
}
