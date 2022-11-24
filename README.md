# Borgbase GLPI Plugin

Plugin with [Borgbase](https://www.borgbase.com/) integration for GLPI to link devices with their backups.

[![License](https://img.shields.io/badge/License-GNU%20AGPLv3-blue.svg)](https://github.com/ticgal/taskdrop/blob/master/LICENSE)
[![Twitter](https://img.shields.io/badge/Twitter-TICgal-blue.svg)](https://twitter.com/ticgalcom)
[![TICgal](https://img.shields.io/badge/Web-TICgal-blue.svg)](https://tic.gal/)
[![Localazy](https://img.shields.io/badge/Translate-Localazy-cyan)](https://localazy.com/p/one-time-secret-glpi#translations)

## Supported versions
- GLPI 10.0.x

# How to configure it

With the first installation you have to go to GLPI configuration panel (Setup > General > Borgbase) and add the Borgbase API key.

Configure the permissions of the profiles that will manage the plugin:
- UPDATE: The user will be able to update the repository data manually.
- CREATE: The user will be able to create links between computer and repository, if the name does not match.
- PURGE: The user will be able to delete manually created links.

## How to use it

By default, if the computer name exactly matches the repository name in Borgbase, the relevant data is stored and can be reviewed in the tab of the computer itself.

Each computer will have a tab with its own Borgbase repository. It can be linked manually in case they do not match.