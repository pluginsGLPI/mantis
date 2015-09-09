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
 * @author Stanislas Kita (teclib)
 * @co-author François Legastelois (teclib)
 * @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
 * @license GPLv3 or (at your option) any later version
 * http://www.gnu.org/licenses/gpl.html
 * @link https://forge.indepnet.net/projects/mantis
 * @since 2014
 *
 * ------------------------------------------------------------------------
 */

/**
 * Class PluginMantisProfile pour la gestion des profiles
 */
class PluginMantisProfile extends CommonDBTM {

   const RIGHT_MANTIS_MANTIS = "mantis:mantis";
   
   static function canCreate() {
      if (isset($_SESSION["glpi_plugin_mantis_profile"])) {
         return ($_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'w');
      }
      return false;
   }

   static function canView() {
      if (isset($_SESSION["glpi_plugin_mantis_profile"])) {
         return ($_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'w' || $_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'r');
      }
      return false;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         return "MantisBT";
      }
      return '';
   }

   /**
    * 
    * Get all rights related to the plugin
    * 
    */
   function getAllRights() {
      $rights = array(
          array('itemtype'  => 'PluginMantisProfile',
                'label'     => __('Use the plugin MantisBT', 'mantis'),
                'field'     => self::RIGHT_MANTIS_MANTIS
          ),
      );
      return $rights;
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $item->getField('id');
         // si le profil n'existe pas dans la base, je l'ajoute
         if (! $prof->GetfromDB($ID)) {
            $prof->createAccess($ID);
         }
         // j'affiche le formulaire
         $prof->showForm($ID);
      }
      return true;
   }

   function showForm($id, $options = array()) {
      $target = $this->getFormURL();
      if (isset($options['target'])) {
         $target = $options['target'];
      }
      
      if (! Profile::canView()) {
         return false;
      }
      
      $canedit = Profile::canCreate();
      $prof = new Profile();
      if ($id) {
         $this->getFromDB($id);
         $prof->getFromDB($id);
      }
      
      if ($canedit) {
         echo "<form action='".$this->getFormURL()."' method='post'>";
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2' class='center b'>" . sprintf(__('%1$s %2$s'), ('rights management :'), Dropdown::getDropdownName("glpi_profiles", $this->fields["id"]));
      echo "</th></tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo '<td>' . __("Use the plugin MantisBT", "mantis") . '</td><td>';
      Profile::dropdownNoneReadWrite("right", $this->fields["right"], 1, 1, 1);
      echo "</td></tr>";
      
      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value=$id>";
         echo "<input type='submit' name='update_user_profile' value=" . __('Update', 'mantis') . "
                class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";

         // Right matrix : need some info to limit to some rights (read / update only)
//       $rights = $this->getAllRights();
//       $prof->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
//                                                          'default_class' => 'tab_bg_2'));
      if ($canedit) {
//          echo "<div class='center'>";
//          echo "<input type='hidden' name='id' value=".$id.">";
//          echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
//          echo "</div>";
         Html::closeForm();
      }
   }

   static function createAdminAccess($ID) {
      $myProf = new self();
      // si le profile n'existe pas déjà dans la table profile de mon plugin
      if (! $myProf->getFromDB($ID)) {
         // ajouter un champ dans la table comprenant l'ID du
         // profil d la personne connecté et le droit d'écriture
         $myProf->add(array(
               'id' => $ID,
               'right' => 'w'
         ));
      }
   }

   function createAccess($ID) {
      $this->add(array(
            'id' => $ID
      ));
   }

   static function changeProfile() {
      $prof = new self();
      if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_mantis_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_mantis_profile"]);
      }
   }

   static function canViewMantis($id) {
      $prof = new self();
      $prof->getFromDB($id);
      if (! $prof)
         return false;
      else {
         if ($prof->fields['right'] & READ)
            return true;
         else
            return false;
      }
   }

   static function canWriteMantis($id) {
      $prof = new self();
      $prof->getFromDB($id);
      if (! $prof)
         return false;
      else {
         if ($prof->fields['right'] & CREATE)
            return true;
         else
            return false;
      }
   }

   /**
    * Init profiles
    *
    **/
   static function translateARight($old_right) {
   	  switch ($old_right) {
   		 case 'r' :
   			return READ;
   			
   		 case 'w':
   			return ALLSTANDARDRIGHT;
   			
   		 case '1':
   		    // Not translated yet
            return '1';
                  
   		 case '0':
   		 case '':
   		 default:
   			return 0;
   	  }
   }
}



