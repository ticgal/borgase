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
include '../../../inc/includes.php';

$plugin = new Plugin();
if (!$plugin->isInstalled('borgbase') || !$plugin->isActivated('borgbase')) {
    Html::displayNotFoundError();
}

$borgbase = new PluginBorgbaseBorgbase();

if (isset($_POST['reload'])) {
    if (Session::haveRight(PluginBorgbaseBorgbase::$rightname, UPDATE)) {
        $computerId = $_POST['computerId'];
        $repoId = $_POST['repoId'];
        $borgbase->reloadRepo($repoId);
    }
    Html::back();
} else if (isset($_POST['unlink'])) {
    if (Session::haveRight(PluginBorgbaseBorgbase::$rightname, PURGE)) {
        $computerId = $_POST['computerId'];
        $repoId = $_POST['repoId'];
        $unlink = $borgbase->unlinkRepo($computerId, $repoId);
        if ($unlink) {
            $changes[0] = $computerId;
            $changes[1] = sprintf(__('%2$s, by user %1$s'), $_SESSION["glpiname"], $repoId);
            $changes[2] = "";

            Log::history(
                $computerId,
                'Computer',
                $changes,
                'PluginBorgbaseBorgbase',
                LOG::HISTORY_DEL_RELATION
            );
        }
    }
    Html::back();
}

if (isset($_POST['assoc']) && Session::haveRight(PluginBorgbaseBorgbase::$rightname, CREATE)) {
    $computerId = isset($_POST['id']) ? intval($_POST['id']) : -1;
    $repoId = $_POST['repoId'];

    if ($computerId === -1) {
        Html::back();
    }

    $repo = $borgbase->getRepo($repoId);
    $linked = $borgbase->linkRepo($repo, $computerId);
    if ($linked) {
        $changes[0] = $computerId;
        $changes[1] = "";
        $changes[2] = sprintf(__('%2$s, by user %1$s'), $_SESSION["glpiname"], $repoId);

        Log::history(
            $computerId,
            'Computer',
            $changes,
            'PluginBorgbaseBorgbase',
            LOG::HISTORY_ADD_RELATION
        );
    }
    Html::back();
}

Html::back();