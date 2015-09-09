<?php

/*
   ------------------------------------------------------------------------
   GLPI Plugin MantisBT
   Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.

   https://forge.indepnet.net/projects/mantis
   ------------------------------------------------------------------------

   LICENSE

   This file is part of GLPI Plugin MantisBT project.

   GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   GLPI Plugin MantisBT is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   GLPI Plugin MantisBT
   @author    Stanislas Kita (teclib')
   @co-author François Legastelois (teclib')
   @co-author Le Conseil d'Etat
   @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
   @license   GPLv3 or (at your option) any later version
              http://www.gnu.org/licenses/gpl.html
   @link      https://forge.indepnet.net/projects/mantis
   @since     2014

   ------------------------------------------------------------------------
 */

define ("PLUGIN_MANTIS_VERSION", "0.84+2.2");

// Minimal GLPI version, inclusive
define ("PLUGIN_MANTIS_GLPI_MIN_VERSION", "0.85");
// Maximum GLPI version, exclusive
define ("PLUGIN_MANTIS_GLPI_MAX_VERSION", "0.92");

/**
 * function to initialize the plugin
 * @global array $PLUGIN_HOOKS
 */
function plugin_init_mantis() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['mantis'] = true;
   $PLUGIN_HOOKS['change_profile']['mantis'] = array('PluginMantisProfile','changeProfile');

   $plugin = new Plugin();

   if (Session::getLoginUserID() && $plugin->isActivated('mantis')) {
      if(plugin_mantis_haveRight("right","w")){
         $PLUGIN_HOOKS['menu_entry']['mantis']  = 'front/config.form.php';
         $PLUGIN_HOOKS['config_page']['mantis'] = 'front/config.form.php';
      }
   }

   $PLUGIN_HOOKS['add_javascript']['mantis'] = array('scripts/scriptMantis.js.php',
                                                   'scripts/jquery-1.11.0.min.js');

   Plugin::registerClass('PluginMantisProfile', 
                     array('addtabon' => array('Profile')));

   Plugin::registerClass('PluginMantisConfig');

   Plugin::registerClass('PluginMantisMantisws');

   Plugin::registerClass('PluginMantisMantis', 
                     array('addtabon' => array('Ticket','Problem')));

    Plugin::registerClass('PluginMantisUserPref',
        array('addtabon' => array('User', 'Preference')));

}

/**
 *function to define the version for glpi for plugin
 * @return array
 */
function plugin_version_mantis() {
   return array(  'name'            => __("MantisBT synchronisation", "mantis"),
                  'version'         => PLUGIN_MANTIS_VERSION,
                  'author'          => 'Stanislas KITA (teclib\')',
                  'license'         => 'GPLv3',
                  'homepage'        => 'https://github.com/teclib/mantis',
                  'minGlpiVersion'  => PLUGIN_MANTIS_GLPI_MIN_VERSION);

}

/**
 * function to check the prerequisites
 * @return boolean
 */
function plugin_mantis_check_prerequisites() {

   if (version_compare(GLPI_VERSION, PLUGIN_MANTIS_GLPI_MIN_VERSION, 'lt') 
      || version_compare(GLPI_VERSION, PLUGIN_MANTIS_GLPI_MAX_VERSION, 'ge')) {
      echo "This plugin requires GLPI >= " . PLUGIN_MANTIS_GLPI_MIN_VERSION . 
         " and GLPI < " . PLUGIN_MANTIS_GLPI_MAX_VERSION;
      return false;
   }

   if (!extension_loaded('soap')) {
      echo "This plugin requires SOAP extension for PHP";
      return false;
   }

   return true;

}


/**
 * function to check the initial configuration
 * @param boolean $verbose
 * @return boolean
 */
function plugin_mantis_check_config($verbose = false) {

   if (true) {
      //your configuration check
      return true;
   }

   if ($verbose) {
      echo _x('plugin', 'Installed / not configured');
   }

   return false;
}

/**
 * function to check rights on plugin
 * @param string $module
 * @param string $right
 * @return boolean
 */
function plugin_mantis_haveRight($module,$right) {
   $matches=array(
   ""  => array("","r","w"), // ne doit pas arriver normalement
   "r" => array("r","w"),
   "w" => array("w"),
   "1" => array("1"),
   "0" => array("0","1"), // ne doit pas arriver non plus
   );
   if (isset($_SESSION["glpi_plugin_mantis_profile"][$module])
         && in_array($_SESSION["glpi_plugin_mantis_profile"][$module],$matches[$right]))
      return true;
   else return false;
}
