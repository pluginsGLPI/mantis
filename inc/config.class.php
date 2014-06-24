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

/**
 * Class PluginMantisConfig pour la partie gestion de la configuration
 */
class PluginMantisConfig extends CommonDBTM {

   /**
   * Function to define if the user have right to create
   * @return bool|booleen
   */
   static function canCreate() {
     return Session::haveRight('config', 'w');
   }


   /**
   * Function to define if the user have right to view
   * @return bool|booleen
   */
   static function canView() {
     return Session::haveRight('config', 'r');
   }


   /**
   * Function to show form to configure the plugin MantisBT
   */
   function showConfigForm() {
      //we recover the first and only record
      $this->getFromDB(1);

      $target = $this->getFormURL();
      if (isset($options['target'])) {
       $target = $options['target'];
      }

      $content  = "<form method='post' action='" . $target . "' method='post'>";
      $content .= "<table class='tab_cadre' >";
      $content .= "<tr>";
      $content .= "<th colspan='6'>" . __("MantisBT plugin setup", "mantis") . "</th>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("MantisBT server IP", "mantis") . "</td>";
      $content .= "<td><input id='host' name='host' type='text' value='" 
                                                       . $this->fields["host"] . "'/></td>";
      $content .= "<td>ex: http(s)://128.65.25.74 or http(s)://serveurName</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("Wsdl file path", "mantis") . "</td>";
      $content .= "<td><input id='url' name='url' type='text' value='" 
                                                         . $this->fields["url"] . "'/></td>";
      $content .= "<td>ex: mantis/api/soap/mantisconnect.php?wsdl</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("MantisBT user login", "mantis") . "</td>";
      $content .= "<td><input  id='login' name='login' type='text' value='" 
                                                         . $this->fields["login"] . "'/></td>";
      $content .= "<td>ex : administrator</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("MantisBT user password", "mantis") . "</td>";
      $content .= "<td><input  id='pwd' name='pwd' type='password' value='" 
                                                            . $this->fields["pwd"] . "'/></td>";

      $content .= "<td></td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("Allow assignation", "mantis") . "</td>";
      $content .= "<td>";

      $content .= Dropdown::showYesNo("enable_assign",$this->fields["enable_assign"],-1,
         array(
            'display'   => false));

      $content .= "</td>";
            $content .= "<td></td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" . __("MantisBT field for the link to the ticket GLPI", "mantis") . "</td>";
      $content .= "<td>";
      $content .= DropDown::showFromArray('champsUrlGlpi', 
                           PluginMantisIssue::$champsMantis, array(
                              'value'     => $this->fields["champsUrlGlpi"],
                              'display'   => false));
      $content .= "</td>";
      $content .= "<td></td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" 
         . __("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)"
            , "mantis") . "</td>";
      $content .= "<td>";
      $content .= DropDown::showFromArray('champsGlpi', 
                           PluginMantisIssue::$champsMantis, array(
                              'value'     => $this->fields["champsGlpi"],
                              'display'   => false));
      $content .= "</td>";
      $content .= "<td></td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td>" 
         . __("Close Glpi ticket when status ticket MantisBT is", "mantis") . "</td>";
      $content .= "<td>";

       $content .= Dropdown::showFromArray('etatMantis', array(),
           array('rand' => '', 'display' => false));

      if (!empty($this->fields["etatMantis"])) {
          $content .= " (".$this->fields["etatMantis"].") ";
      }



      $content .= "</td>";
      $content .= "<td>Tester la connexion pour faire apparaître les états Mantis</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input type='hidden' name='id' value='1' class='submit'>";
      $content .= "<input id='update' type='submit' name='update' value='" 
                                             . __("Update", "mantis") . "' class='submit'></td>";
      $content .= "<td><input id='test' onclick='testConnexionMantisWS();'  value='"
                                 . __("Test the connection", "mantis") . "' class='submit'></td>";
      $content .= "<td><div id='infoAjax'/></td>";
      $content .= "</tr>";

      $content .= "</table>";
      
      $content .= Html::closeForm(false);

      echo $content;
    }

}