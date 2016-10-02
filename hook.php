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

/**
 * Install all necessary elements for the plugin
 *
 * @return boolean True if success
 */
function plugin_mantis_install() {

   $migration = new Migration(PLUGIN_MANTIS_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginMantis' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }
   return true;
}

/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_mantis_uninstall() {

   $migration = new Migration(PLUGIN_MANTIS_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginMantis' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall($migration);
         }
      }
   }
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
