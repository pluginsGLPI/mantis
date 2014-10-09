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

        //HOST OF MANTIS SERVER
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("MantisBT server IP", "mantis") . "</td>";
        $content .= "<td><input id='host' name='host' type='text' value='".$this->fields["host"]."'/></td>";
        $content .= "<td>ex: http(s)://128.65.25.74 or http(s)://serveurName</td>";
        $content .= "</tr>";

        //PATH FOR WSDL FILE
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Wsdl file path", "mantis") . "</td>";
        $content .= "<td><input id='url' name='url' type='text' value='" . $this->fields["url"] . "'/></td>";
        $content .= "<td>ex: mantis/api/soap/mantisconnect.php?wsdl</td>";
        $content .= "</tr>";

        //MANTIS USER LOGIN
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("MantisBT user login", "mantis") . "</td>";
        $content .= "<td><input  id='login' name='login' type='text' value='" . $this->fields["login"] . "'/></td>";
        $content .= "<td>ex : administrator</td>";
        $content .= "</tr>";

        //MANTIS USER PASSWORD
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("MantisBT user password", "mantis") . "</td>";
        $content .= "<td><input  id='pwd' name='pwd' type='password' value='"  . $this->fields["pwd"] . "'/></td>";
        $content .= "<td></td>";
        $content .= "</tr>";

        //ASSIGNATION OPTION
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Allow assignation", "mantis") . "</td>";
        $content .= "<td>";
        $content .= Dropdown::showYesNo("enable_assign",$this->fields["enable_assign"],-1,array('display'=>false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";

        //OPTION TO NEUTRALIZE ESCLATION SWITH GLPI STATUS
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Neutralize the escalating to MantisBT when the status of the GLPI tickets is", "mantis") . "</td>";
        $content .= "<td>";
        $content .= self::dropdownStatus(array('showtype' => 'normal','name'=>'neutralize_escalation',
        'value' => $this->fields["neutralize_escalation"],'display' => false,'none' => false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";

        //OPTION TO SET STATUS   GLPI AFTER ESCALATION
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Status of glpi ticket after escalation to MantisBT", "mantis") . "</td>";
        $content .= "<td>";
        $content .= self::dropdownStatus(array('showtype' => 'normal','name'=>'status_after_escalation' ,
        'value' => $this->fields["status_after_escalation"],'display' => false,'none' => true));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";

        //OPTION TO SHOW DELETE OPTION
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Show option 'Delete the  MantisBT ticket' ", "mantis") . "</td>";
        $content .= "<td>";
        $content .= Dropdown::showYesNo('show_option_delete',$this->fields["show_option_delete"],-1,array('rand' => false,'display' => false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";

        //TYPE ATTCHMANT
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("Attachment type transfered to MantisBT", "mantis") . "</td>";
        $content .= "<td>";
        $content .= DocumentCategory::dropdown(array('value' => $this->fields["doc_categorie"],'name' => 'doc_categorie','rand' => false,'display' => false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";


        //MANTIS FIELD FOR GLPI FIELD
        /*$content .= "<tr class='tab_bg_1'>";
        $content .= "<td>"
        . __("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)", "mantis") . "</td>";
        $content .= "<td>";
        $content .= DropDown::showFromArray('champsGlpi',PluginMantisIssue::$champsMantis, array(
            'value' => $this->fields["champsGlpi"],'display' => false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";*/

        //MANTIS FIELD FOR GLPI URL
        /*$content .= "<tr class='tab_bg_1'>";
        $content .= "<td>" . __("MantisBT field for the link to the ticket GLPI", "mantis") . "</td>";
        $content .= "<td>";
        $content .= DropDown::showFromArray('champsUrlGlpi',PluginMantisIssue::$champsMantis, array('value' => $this->fields["champsUrlGlpi"],'display'=> false));
        $content .= "</td>";
        $content .= "<td></td>";
        $content .= "</tr>";*/

        //MANTIS STATUS TO CLOSE GLPI TICKET
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td>". __("Close Glpi ticket when status ticket MantisBT is", "mantis") . "</td>";
        $content .= "<td>";
        $content .= Dropdown::showFromArray('etatMantis', array(), array('rand' => '', 'display' => false));
        if (!empty($this->fields["etatMantis"])) {
        $content .= " (".$this->fields["etatMantis"].") ";
        }
        $content .= "</td>";
        $content .= "<td>Tester la connexion pour faire apparaître les états Mantis</td>";
        $content .= "</tr>";

        //INPUT BUTTON
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td><input type='hidden' name='id' value='1' class='submit'>";
        $content .= "<input id='update' type='submit' name='update' value='". __("Update", "mantis") . "' class='submit'></td>";
        $content .= "<td><input id='test' onclick='testConnexionMantisWS();'  value='". __("Test the connection", "mantis") . "' class='submit'></td>";
        $content .= "<td><div id='infoAjax'/></td>";
        $content .= "</tr>";

        $content .= "</table>";
        $content .= Html::closeForm(false);

        echo $content;
    }


    /**
     * Dropdown of object status
     *
     * @since version 0.84 new proto
     *
     * @param $options   array of options
     *  - name     : select name (default is status)
     *  - value    : default value (default self::INCOMING)
     *  - showtype : list proposed : normal, search or allowed (default normal)
     *  - display  : boolean if false get string
     * - none  : display none option : default false
     *
     * @return nothing (display)
     **/
    static function dropdownStatus(array $options=array()) {

        $p['name']      = 'status';
        $p['value']     = 0;
        $p['showtype']  = 'normal';
        $p['display']   = true;
        $p['none']   = false;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }


        switch ($p['showtype']) {
            case 'allowed' :
                $tab = Ticket::getAllowedStatusArray($p['value']);
                break;

            case 'search' :
                $tab = Ticket::getAllStatusArray(true);
                break;

            default :
                $tab = Ticket::getAllStatusArray(false);
                break;
        }

        if($p['none'] == true){
            array_unshift($tab," ---- ");
        }



        $output = "<select name='".$p['name']."'>";
        foreach ($tab as $key => $val) {
            $output .=  "<option value='$key' ".(($p['value'] == $key)?" selected ":"").">$val</option>";
        }
        $output .=  "</select>";

        if ($p['display']) {
            echo $output;
        } else {
            return $output;
        }
    }

}