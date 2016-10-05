<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015-2016 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define("PLUGIN_MANTIS_VERSION", "3.0.0");

// Minimal GLPI version, inclusive
define("PLUGIN_MANTIS_MIN_GLPI", "0.85");
// Maximum GLPI version, exclusive
define("PLUGIN_MANTIS_MAX_GLPI", "9.2");

/**
 * function to initialize the plugin
 *
 * @global array $PLUGIN_HOOKS
 */
function plugin_init_mantis() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['mantis'] = true;

   $PLUGIN_HOOKS['change_profile']['mantis'] = array('PluginMantisProfile', 'changeProfile');
   
   $plugin = new Plugin();
   
   if (Session::getLoginUserID() && $plugin->isActivated('mantis')) {

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['mantis'] = 'front/config.form.php';
      }

      $PLUGIN_HOOKS['add_javascript']['mantis'] = array(
            'scripts/scriptMantis.js.php'
      );
      
      if (Session::haveRight('profile', UPDATE)) {
         Plugin::registerClass('PluginMantisProfile', 
                                 array('addtabon' => 'Profile'));
      }
      
      Plugin::registerClass('PluginMantisConfig');
      
      Plugin::registerClass('PluginMantisMantisws');

      if (Session::haveRightsOr('plugin_mantis_use', array(READ, UPDATE))) {
         Plugin::registerClass('PluginMantisMantis', 
                                 array('addtabon' => array('Ticket', 'Problem', 'Change')));
      
         Plugin::registerClass('PluginMantisUserPref', 
                                 array('addtabon' => array('User', 'Preference')));
      }
   }
}

/**
 * function to define the version for glpi for plugin
 *
 * @return array
 */
function plugin_version_mantis() {
   return array(
         'name' => __("MantisBT synchronization", "mantis"),
         'version' => PLUGIN_MANTIS_VERSION,
         'author' => 'TECLIB\'',
         'license' => 'GPLv3',
         'homepage' => 'https://github.com/pluginsGLPI/mantis',
         'minGlpiVersion' => PLUGIN_MANTIS_MIN_GLPI
   );
}

/**
 * function to check the prerequisites
 *
 * @return boolean
 */
function plugin_mantis_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_MANTIS_MIN_GLPI,'lt')
      || version_compare(GLPI_VERSION, PLUGIN_MANTIS_MAX_GLPI,'ge')
   ) {
      echo sprintf(
         __('This plugin requires GLPi > %1$s and < %2$s'),
               PLUGIN_MANTIS_MIN_GLPI,
               PLUGIN_MANTIS_MAX_GLPI
      );

      return false;
   }
   
   if (!extension_loaded('soap')) {
      _e("This plugin requires SOAP extension for PHP");
      return false;
   }
   
   return true;
}

/**
 * function to check the initial configuration
 *
 * @param boolean $verbose
 * @return boolean
 */
function plugin_mantis_check_config($verbose = false) {
   if (true) {
      // your configuration check
      return true;
   }
   
   if ($verbose) {
      echo _x('plugin', 'Installed / not configured');
   }
   
   return false;
}