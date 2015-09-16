<?php

/*
 * ------------------------------------------------------------------------
 * GLPI Plugin MantisBT
 * Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.
 *
 * https://forge.indepnet.net/projects/mantis
 * ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI Plugin MantisBT project.
 *
 * GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GLPI Plugin MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 * @package GLPI Plugin MantisBT
 * @author Stanislas Kita (teclib')
 * @co-author François Legastelois (teclib')
 * @co-author Le Conseil d'Etat
 * @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
 * @license GPLv3 or (at your option) any later version
 * http://www.gnu.org/licenses/gpl.html
 * @link https://forge.indepnet.net/projects/mantis
 * @since 2014
 *
 * ------------------------------------------------------------------------
 */

/**
 * function to install the plugin
 *
 * @return boolean
 */
function plugin_mantis_install() {
   require_once ('inc/mantis.class.php');
   require_once ('inc/userpref.class.php');
   require_once ('inc/config.class.php');
    
   global $DB;
   
   $migration = new Migration(PLUGIN_SIMCARD_VERSION);
   $currentVersion = plugin_mantis_currentVersion();
   $migration->setVersion($currentVersion);
   
   if ($currentVersion == 0) {
      PluginMantisMantis::install($migration);
      PluginMantisUserpref::install($migration);
      PluginMantisConfig::install($migration);
   } else {
      PluginMantisMantis::upgrade($migration);
      PluginMantisUserpref::upgrade($migration);
      PluginMantisConfig::upgrade($migration);
   }
   return true;
}

/**
 * function to uninstall the plugin
 *
 * @return boolean
 */
function plugin_mantis_uninstall() {
   require_once ('inc/mantis.class.php');
   require_once ('inc/userpref.class.php');
   require_once ('inc/config.class.php');
   
   PluginMantisMantis::uninstall();
   PluginMantisUserpref::uninstall();
   PluginMantisConfig::uninstall();
   return true;
}

// Define Additionnal search options for types (other than the plugin ones)
function plugin_mantis_getAddSearchOptions($itemtype) {
   $sopt = array();
   if ($itemtype == 'Ticket') {
      
      $sopt['common'] = "MantisBT";
      
      $sopt[78963]['table'] = 'glpi_plugin_mantis_mantis';
      $sopt[78963]['field'] = 'idMantis';
      $sopt[78963]['searchtype'] = 'equals';
      $sopt[78963]['nosearch'] = true;
      $sopt[78963]['datatype'] = 'bool';
      $sopt[78963]['name'] = __('ticket linked to mantis', 'mantis');
      $sopt[78963]['joinparams'] = array(
            'jointype' => "itemtype_item"
      );
   } else if ($itemtype == 'Problem') {
      $sopt['common'] = "MantisBT";
      
      $sopt[78964]['table'] = 'glpi_plugin_mantis_mantis';
      $sopt[78964]['field'] = 'id';
      $sopt[78964]['searchtype'] = 'equals';
      $sopt[78964]['nosearch'] = true;
      $sopt[78964]['datatype'] = 'bool';
      $sopt[78964]['name'] = __('problem linked to mantis', 'mantis');
      $sopt[78964]['joinparams'] = array(
            'jointype' => "itemtype_item"
      );
   }
   return $sopt;
}

function plugin_mantis_giveItem($type, $ID, $data, $num) {
   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];
   
   switch ($table . '.' . $field) {
      case "glpi_plugin_mantis_mantis.idMantis" :
         return Dropdown::getYesNo($data["ITEM_$num"]);
         break;
   }
   
   return "";
}

/**
 *
 * Determine if the plugin should be installed or upgraded
 *
 * Returns 0 if the plugin is not yet installed
 * Returns version string if the plugin is already installed
 *
 * @since 0.85+2.2
 *
 * @return string
 */
function plugin_mantis_currentVersion() {
   static $currentVersion = null;
   
   if ($currentVersion === null) {
      if (TableExists("glpi_plugin_mantis_mantis")) {
         if (!FieldExists("glpi_plugin_mantis_mantis", "version")) {
            // Version < 0.85+2.2 : no version control for upgrade
            // No known previous releases, give up for safety
            // Needs further development
            echo ('Cannot upgrade from previous versions for safety reason: unknown database model');
            die ('\n Please, contact a developer');
         } else {
            // Version >= 0.85+2.2
            $conf = new PluginMantisConfig();
            if (!$conf->getFromDB(1)) {
               die('Unable to get current version of the plugin.');
            } else {
               $currentVersion = $conf->fields['version'];
            }
         }
      } else {
         $currentVersion = 0;
      }
   }
   return $currentVersion;
}