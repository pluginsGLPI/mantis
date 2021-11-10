<?php

/**
 * -------------------------------------------------------------------------
 * Mantis plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Mantis.
 *
 * Mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mantis. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2014-2022 by Mantis plugin team.
 * @license   AGPLv3 https://www.gnu.org/licenses/agpl-3.0.html
 * @link      https://github.com/pluginsGLPI/mantis
 * -------------------------------------------------------------------------
 */

define("PLUGIN_MANTIS_VERSION", "4.4.1");

// Minimal GLPI version, inclusive
define("PLUGIN_MANTIS_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_MANTIS_MAX_GLPI", "10.0.99");

/**
 * function to initialize the plugin
 *
 * @global array $PLUGIN_HOOKS
 */
function plugin_init_mantis() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['mantis'] = true;

   $PLUGIN_HOOKS['change_profile']['mantis'] = ['PluginMantisProfile', 'changeProfile'];

   $plugin = new Plugin();

   if (Session::getLoginUserID() && $plugin->isActivated('mantis')) {

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['mantis'] = 'front/config.form.php';
      }

      $PLUGIN_HOOKS['add_javascript']['mantis'] = [
            'scripts/scriptMantis.js.php'
      ];

      if (Session::haveRight('profile', UPDATE)) {
         Plugin::registerClass('PluginMantisProfile',
                                 ['addtabon' => 'Profile']);
      }

      Plugin::registerClass('PluginMantisConfig');

      Plugin::registerClass('PluginMantisMantisws');

      if (Session::haveRightsOr('plugin_mantis_use', [READ, UPDATE])) {
         Plugin::registerClass('PluginMantisMantis',
                                 ['addtabon' => ['Ticket', 'Problem', 'Change']]);

         Plugin::registerClass('PluginMantisUserPref',
                                 ['addtabon' => ['User', 'Preference']]);
      }

      $PLUGIN_HOOKS['post_prepareadd']['mantis'] = [
         'ITILSolution' => ['PluginMantisMantis', 'forceSolutionUserOnSolutionAdd'],
      ];
   }

   // Encryption
   $PLUGIN_HOOKS['secured_fields']['metabase'] = ['glpi_plugin_mantis_configs.pwd'];
}

/**
 * function to define the version for glpi for plugin
 *
 * @return array
 */
function plugin_version_mantis() {

   return [
      'name' => __("MantisBT synchronization", "mantis"),
      'version' => PLUGIN_MANTIS_VERSION,
      'author'  => 'TECLIB\'',
      'license' => 'GPLv3',
      'homepage'=>'https://github.com/pluginsGLPI/mantis',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_MANTIS_MIN_GLPI,
            'max' => PLUGIN_MANTIS_MAX_GLPI,
         ],
         'php' => [
            'exts' => [
               'soap'     => [
                  'required' => true,
               ]
            ]
         ]
      ]
   ];
}

