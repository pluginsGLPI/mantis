<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

define("PLUGIN_MANTIS_VERSION", "4.2.1");

// Minimal GLPI version, inclusive
define("PLUGIN_MANTIS_MIN_GLPI", "9.3");
// Maximum GLPI version, exclusive
define("PLUGIN_MANTIS_MAX_GLPI", "9.4");

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
            'dev' => true, //Required to allow 9.2-dev
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

/**
 * Check pre-requisites before install
 *
 * @return boolean
 */
function plugin_mantis_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   if (!method_exists('Plugin', 'checkGlpiVersion')) {
      $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
      $matchMinGlpiReq = version_compare($version, PLUGIN_MANTIS_MIN_GLPI, '>=');
      $matchMaxGlpiReq = version_compare($version, PLUGIN_MANTIS_MAX_GLPI, '<');

      if (!$matchMinGlpiReq || !$matchMaxGlpiReq) {
         echo vsprintf(
            'This plugin requires GLPI >= %1$s and < %2$s.',
            [
               PLUGIN_MANTIS_MIN_GLPI,
               PLUGIN_MANTIS_MAX_GLPI,
            ]
         );
         return false;
      }
   }

   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_mantis_check_config($verbose = false) {
   return true;
}
