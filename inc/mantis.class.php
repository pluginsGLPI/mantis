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
 * Class PluginMantis -> class générale du plugin Mantis
 */
class PluginMantisMantis extends CommonDBTM {

   static function install() {
      $cron = new CronTask;
      if (!$cron->getFromDBbyName(__CLASS__, 'mantis')) {
         CronTask::Register(__CLASS__, 'mantis', 7 * DAY_TIMESTAMP,
            array('param' => 24, 'mode' => CronTask::MODE_EXTERNAL));
      }
   }

   static function uninstall() {
      CronTask::Unregister(__CLASS__);
   }

   static function cronMantis($task) {
      self::updateTicket();
      return true;
   }

   static function cronInfo($name) {
      return array('description' => __("Update ticket", "mantis"));
   }

   static function updateTicket() {

      Toolbox::logInFile("mantis", __("Starting update tickets cron", "mantis"));

      $list_ticket_mantis = array();

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      if ($conf->getField('etatMantis')) {

         $etat_mantis = $conf->getField('etatMantis');
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();

         //on recuper l'id des ticket Glpi linké
         $res = self::getTicketWhichIsLinked();

         while ($row = $res->fetch_assoc()) {

            $ticket_glpi = new Ticket();
            $ticket_glpi->getFromDB($row['idTicket']);

            $list_link = self::getLinkBetweenTicketGlpiAndTicketMantis($row['idTicket']);
            $list_ticket_mantis = array();

            while ($line = $list_link->fetch_assoc()){

               $mantis = $ws->getIssueById($line['idMantis']);
               $list_ticket_mantis[] = $mantis;

            }

            if(self::getAllSameStatusChoiceByUser ($list_ticket_mantis,
                  $etat_mantis) && $ticket_glpi->fields['status'] != 5){

               $info_solved = self::getInfoSolved($list_ticket_mantis);
               $ticket_glpi->fields['status'] = Ticket::SOLVED;
               $ticket_glpi->fields['closedate'] = date("Y-m-d");
               $ticket_glpi->fields['solvedate'] = date("Y-m-d");
               $ticket_glpi->fields['solution'] = $info_solved;
               $ticket_glpi->update($ticket_glpi->fields);
            }

         }

         Toolbox::logInFile("mantis", __("Ending update tickets cron", "mantis"));

      } else {
         Toolbox::logInFile("mantis", 
          __("Error on launching updateTicket cron because MantisBT status is not providing.", "mantis"));
      }
   }

   private static function getAllSameStatusChoiceByUser($list_ticket_mantis,$status){
      $diferrent  =false;
      if(count($list_ticket_mantis) == 0 )return false;
      for($i = 0; $i <= count($list_ticket_mantis) ; $i++){
         if($list_ticket_mantis[$i]->status->name != $status)$diferrent = true;
         break;
      }
      if($diferrent) return false;
      else return true;
   }


   /**
    * function to get id ticket Glpi which is linked
    * @return Query
    */
   private static function getTicketWhichIsLinked() {
         global $DB;

         return $DB->query("SELECT DISTINCT (`glpi_plugin_mantis_mantis`.`idTicket`) 
                            FROM `glpi_plugin_mantis_mantis`, `glpi_tickets`
                            WHERE `glpi_plugin_mantis_mantis`.`idTicket` = `glpi_tickets`.`id`");
   }


   /**
    * Function to get link between glpi ticket and mantisBT ticket for an glpi ticket
    * @param $idTicket
    * @return Query
    */
   private static function getLinkBetweenTicketGlpiAndTicketMantis($idTicket) {
      global $DB;

      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`idTicket` = '" . Toolbox::cleanInteger($idTicket)."'");
   }


   /**
    * Function to get information in Note for each ticket MAntisBT
    * @param $list_ticket_mantis
    * @return string
    */
   private static function getInfoSolved($list_ticket_mantis) {

      $info = "";
      foreach ($list_ticket_mantis as &$ticket) {
         $notes = $ticket->notes;
         foreach ($notes as &$note) {
            $info .= $ticket->id." - ".$note->reporter->name." : ".$note->text."<br/>";
         }
      }
      return $info;

   }


   /**
    * Define tab name
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $id = $_SESSION['glpiactiveprofile']['id'];
      if (PluginMantisProfile::canViewMantis($id) || PluginMantisProfile::canWriteMantis($id))
         if ($item->getType() == 'Ticket') {
            return __("MantisBT","mantis");
         }
      return '';
   }

   static function getTypeName($nb = 0) {
      return __("MantisBT","mantis");
   }

   static function canCreate() {
      return Session::haveRight('ticket', 'w');
   }

   static function canView() {
      return Session::haveRight('ticket', 'r');
   }


   /**
    * Define tab content
    **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Ticket') {
         $mon_plugin = new self();
         //$ID = $item->getField('id');
         // j'affiche le formulaire
         $mon_plugin->showForm($item);
      }
      return true;
   }


   /**
    * Function to show the form of plugin
    * @param $item
    */
   public function showForm($item) {
      global $CFG_GLPI;
      $ws = new PluginMantisMantisws();
      $conf = new PluginMantisConfig();
      //recover the first and only record
      $conf->getFromDB(1);

      //check if Web Service Mantis works fine
      if ($ws->testConnectionWS($conf->getField('host'), $conf->getField('url'),
            $conf->getField('login'), $conf->getField('pwd'))) {


         // if canView or canWrite
         if (PluginMantisProfile::canViewMantis($_SESSION['glpiactiveprofile']['id'])
             || PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id'])) {

            $this->getFormForDisplayInfo($item);

            // if canWrite
            if (PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id'])) {
               $this->displayBtnToLinkissueGlpi($item);
            }
         }

      } else {

         $content = "<div class='center'>";
         $content .= "<img src='" . $CFG_GLPI["root_doc"] ."/pics/warning.png'  alt='warning'>";
         $content .= "<b>". __("Thank you configure the mantis plugin", "mantis") . "</b>";
         $content .= "</div>";
         echo $content;
      }

   }

    /**
     * function to show action give by plugin
     * @param $item
     */
   public function displayBtnToLinkissueGlpi($item) {
      global $CFG_GLPI;

      $content = "<div id='popupLinkGlpiIssuetoMantisIssue' ></div>";
      $content .= "<div id='popupLinkGlpiIssuetoMantisProject' ></div>";

      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisIssue',
         $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToIssue&idTicket=' .
         $item->fields['id'], array('title'  => __("MantisBT actions", "mantis"),
                                    'width'  => 520,
                                    'height' => 115));

      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
         $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' .
         $item->fields['id'], array('title'  => __("MantisBT actions", "mantis"),
                                    'width'  => 620,
                                    'height' => 390));

      $content .= "<table id='table1'  class='tab_cadre_fixe' >";
      $content .= "<th colspan='6'>".__("MantisBT actions", "mantis")."</th>";

      $content .= "<tr class='tab_bg_1'>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisIssue.show();'  value='" .
         __('Link to an existing MantisBT ticket', 'mantis') ."' class='submit' 
         style='width : 200px;'></td>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisProject.show();'  value='" .
         __('Create a new MantisBT ticket', 'mantis') ."' class='submit'
         style='width : 250px;'></td>";

      $content .= "</tr>";
      $content .= "</table>";

      echo $content;

   }


   /**
    * Form for delete Link Between Glpi ticket and MantisBT ticket
    * or MantisBT ticket
    * @param $id_link
    * @param $id_ticket
    * @param $id_mantis
    */
   public function getFormToDelLinkOrIssue($id_link, $id_ticket, $id_mantis){
      global $CFG_GLPI;

      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();
      $issue = $ws->getIssueById($id_mantis);

      $content = "<form action='#' id=".$id_link.">";

      $content .= "<table id=".$id_link." class='tab_cadre' cellpadding='5' >";

      $content .= "<th colspan='2'>".__("What do you do ?","mantis")."</th>";

      $content .= "<tr class='tab_bg_1' >";
      $content .= "<td><INPUT type='checkbox'  id='deleteLink".$id_link."' >";
      $content .= __("Delete link of the MantisBT ticket","mantis")."</td>";
      $content .= "<td>".__("(Does not delete the MantisBT ticket)","mantis")."</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      if (!$issue) {
         $content .= "<td><INPUT type='checkbox' disabled id='deleteIssue" . $id_link . "' >";
         $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
      } else {
         $content .= "<td><INPUT type='checkbox' id='deleteIssue" . $id_link . "' >";
         $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
      }
      $content .= "<td>".__("(Also removes the link in GLPI)","mantis")."</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input  id=" . $id_link . "  name='delo' value='" . __("Delete", "mantis") .
         "' class='submit' onclick='delLinkAndOrIssue(" .
         $id_link . "," . $id_mantis . "," . $id_ticket . ");'></td>";
      $content .= "<td><div id='infoDel" . $id_link . "' ></div>";
      $content .= "<img id='waitDelete" . $id_link . "' src='".$CFG_GLPI["root_doc"].
         "/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      $content .= "</tr>";

      $content .= "<input type='hidden' name='idMantis".$id_link."' id='idMantis' value=" .
         $id_link . "       >";
      $content .= "<input type='hidden' name='id".$id_link."'       id='id'       value=" .
         $id_mantis . " >";
      $content .= "<input type='hidden' name='idTicket".$id_link."' id='idticket' value=" .
         $id_ticket . " >";

      $content .= "</table>";

      $content .= Html::closeForm(false);

      echo $content;
   }




   /**
    * Form to link glpi ticket to Mantis Ticket
    * @param $id_ticket
    */
   public function getFormForLinkGlpiTicketToMantisTicket($id_ticket) {
      global $CFG_GLPI;

      $content = "<form action='#' >";
      $content.= "<table class='tab_cadre'cellpadding='5'>";
      $content.= "<th colspan='6'>".__('Link to an existing MantisBT ticket','mantis')."</th>";

      $content.= "<tr class='tab_bg_1'>";
      $content.= "<th width='100'>".__('Id Ticket','mantis')."</th>";
      $content.= "<td ><input size=35 id='idMantis' type='text' name='idMantis' onkeypress=\"if(event.keyCode==13)linkIssueglpiToIssueMantis();\" /></td>";
      $content.= "</tr>";

      $content.= "<tr class='tab_bg_1'>";
      $content.= "<td><input  id='linktoIssue'  name='linktoIssue' value='Lier'
        class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";

      $content.= "<td width='150' height='20'>";
      $content.= "<div id='infoLinIssueGlpiToIssueMantis' ></div>";
      $content.= "<img id='waitForLinkIssueGlpiToIssueMantis'  src='".$CFG_GLPI['root_doc'].
         "/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      $content.= "</tr>";

      $content.= "<input type='hidden' name='idTicket' id='idTicket' value=" .  $id_ticket . " >";
      $content.= "<input type='hidden' name='user' id='user' value=" . Session::getLoginUserID(). " >";
      $content.= "<input type='hidden' name='dateEscalade' id='dateEscalade' value=" .date("Y-m-d") . " >";

      $content.= "</table>";

      $content.= Html::closeForm(false);

      echo $content;
   }


   /**
    * Form to link glpi ticket to mantis project
    * @param $id_ticket
    */
   public function getFormForLinkGlpiTicketToMantisProject($id_ticket) {
      global $CFG_GLPI;

      $content  = "<form action='#' >";
      $content .= "<table id='table2' class='tab_cadre' cellpadding='5'>";
      $content .= "<tr class='headerRow'><th colspan='6'>".
         __("Create a new MantisBT ticket","mantis")."</th></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>Nom du projet</th>";
      $content .= "<td id='tdSearch' height='24'>";

       $content .= "<input  id='nameMantisProject' type='text'  name='resume'  onkeypress=\"if(event.keyCode==13)findProjectByName();\" />";
      $content .= "<img id='searchImg' alt='rechercher' src='".$CFG_GLPI['root_doc']."/pics/aide.png'
         onclick='findProjectByName();'style='cursor: pointer;padding-left:5px; padding-right:5px;'/></td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>".__("Category","mantis")."</th><td>";
      $content .= Dropdown::showFromArray('categorie', array(),
                                          array('rand' => '' ,'display' => false));
      $content .= "</td></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>".__("Summary","mantis")."</th>";
      $content .= "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>".__("Description","mantis")."</th>";
      $content .= "<td><textarea  rows='5' cols='55' name='description'
        id='description'></textarea></td></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>".__("Steps to reproduce","mantis")."</th>";
      $content .= "<td><textarea  rows='5' cols='55' name='stepToReproduce'
        id='stepToReproduce'></textarea></td></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>".__("Attachments","mantis")."</th>";
      $content .= "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' >".
         __("To forward attachments","mantis")."</td></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=" .
         $id_ticket . " class='submit'>";
      $content .= "<input type='hidden' class='center' name='user' id='user' value=" .
         Session::getLoginUserID() . " class='submit'>";
      $content .= "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=" .
         date("Y-m-d") . " class='submit'>";
      $content .= "<input  id='linktoProject' onclick='linkIssueglpiToProjectMantis();
         'name='linktoProject' value='".__("Link","mantis")."' class='submit'></td>";

      $content .= "<td width='150' >";
      $content .= "<div id='infoLinkIssueGlpiToProjectMantis' ></div>";
      $content .= "<img id='waitForLinkIssueGlpiToProjectMantis'
         src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/please_wait.gif' style='display:none;'/>";
      $content .= "</td>";

      $content .= "</table>";

      $content .= Html::closeForm(false);

      echo $content;
   }


   /**
    * Form to display information from MantisBT
    * @param $item
    */
   private function getFormForDisplayInfo($item) {
      global $CFG_GLPI, $DB;

      $can_write = PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id']);

      $content = "";

      //on recupere l'ensemble des lien entre ticket glpi et ticket(s) mantis
      $res = $this->getLinkBetweenGlpiAndMantis($item);

      if ($res->num_rows > 0) {

         $content .= "<table id='table1'  class='tab_cadre_fixe' >";
         $content .= "<th colspan='8'>".__("Already MantisBT tickets linked", "mantis")."</th>";

         $content .= "<tr class='headerRow'>";
         $content .= "<th>" . __("Link", "mantis") . "</th>";
         $content .= "<th>" . __("ID", "mantis") . "</th>";
         $content .= "<th>" . __("Summary", "mantis") . "</th>";
         $content .= "<th>" . __("Project", "mantis") . "</th>";
         $content .= "<th>" . __("Status", "mantis") . "</th>";
         $content .= "<th>" . __("OpenDate", "mantis") . "</th>";
         $content .= "<th>" . __("User", "mantis") . "</th>";
         $content .= "<th></th>";
         $content .= "</tr>";

         $user = new User();
         $conf = new PluginMantisConfig();
         $ws   = new PluginMantisMantisws();
         $ws->initializeConnection();

         while ($row = $res->fetch_assoc()) {

            $user->getFromDB($row["user"]);
            $issue = $ws->getIssueById($row["idMantis"]);
            $conf->getFromDB(1);

            $content .= '<div id=\'popupToDelete' . $row['id'] . '\'></div>';

            Ajax::createModalWindow('popupToDelete' . $row['id'],
               $CFG_GLPI['root_doc'].'/plugins/mantis/front/mantis.form.php?action=deleteIssue&id=' .
               $row['id'] . '&idTicket=' . $row['idTicket'] . '&idMantis=' . $row['idMantis'],
               array('title'  => __("Delete", "mantis"),
                     'width'  => 550,
                     'height' => 160));

            if (!$issue) {

               $content .= "<tr>";
               $content .= "<td class='center'><img src='".$CFG_GLPI['root_doc'].
                  "/plugins/mantis/pics/cross16.png'/></td>";
               $content .= "<td>" . $row["idMantis"] . "</td>";

               if ($can_write) {
                  $content .= "<td class='center' colspan='5'>" .
                     __('This ticket does not in the  MantisBT database', 'mantis') . "</td>";
                  $content .= "<td class = 'center'> <img src='".$CFG_GLPI['root_doc'].
                     "/plugins/mantis/pics/bin16.png'  onclick='popupToDelete" . $row['id'] .
                     ".show()';   style='cursor: pointer;' title=" . __("Delete link", "mantis") .
                     "/></td>";
                  $content .= "</tr>";
               } else {
                  $content .= "<td colspan='6' class='center'>" 
                     . __('This ticket does not in the  MantisBT database','mantis') . "</td>";
                  $content .= "</tr>";
               }

            } else {
                $implode = explode("/",$conf->fields['url']);

               $content .= "<tr>";
               $content .= "<td class='center'>";
               $content .= "<a href='".$conf->fields['host']."/".$implode[0]."/view.php?id=" . $issue->id . "' target='_blank' >";
               $content .= "<img src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/arrowRight16.png'/>";
               $content .= "</a></td>";
               $content .= "<td class='center'>" . $issue->id . "</td>";
               $content .= "<td class='center'>" . stripslashes($issue->summary) . "</td>";
               $content .= "<td class='center'>" . $issue->project->name . "</td>";
               $content .= "<td class='center'>" . $issue->status->name . "</td>";
               $content .= "<td class='center'>" . $row["dateEscalade"] . "</td>";
               $content .= "<td class='center'>" . $user->getName() . "</td>";

               if ($can_write) {
                  $content .= "<td class = 'center'>";
                  $content .= "<img src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/bin16.png'
                  onclick='popupToDelete" . $row['id'] . ".show()';
                  style='cursor: pointer;' title='" . __("Delete link", "mantis") . "'/></td>";
               } else {
                  $content .= "<td ></td>";
                  $content .= "</tr>";
               }
            }
         }
         $content .= "</table>";

      } else {

         $content .= "<table class='tab_cadre_fixe' cellpadding='5'>";
         $content .= "<tr class='headerRow'><th colspan='6'>" .
            __("Info ticket MantisBT", "mantis") . "</th></tr>";
         $content .= "<td class='center'>" .
            __("GLPI ticket is not attached to any MantisBT ticket(s)", "mantis") . "</td>";
         $content .= "</table>";

      }

      echo $content;
   }


   /**
    * Function to check if link between glpi issue and mantis issue exist
    * @param $id_ticket
    * @param $id_mantis
    * @return true if exist else false
    */
   public function IfExistLink($id_ticket, $id_mantis) {
      return $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`idTicket` = '" .
         Toolbox::cleanInteger($id_ticket) . "'  AND  `" . "`.`idMantis` = '" .
         Toolbox::cleanInteger($id_mantis) . "'");
   }


   /**
    * Function to find all links record for an item
    * @param $item
    * @return Query
    */
   public function getLinkBetweenGlpiAndMantis($item){
      global $DB;

      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`idTicket` = '" . Toolbox::cleanInteger($item->getField('id')) . "'
                        order by `glpi_plugin_mantis_mantis`.`dateEscalade`");

   }


   /**
    * Function to find all links record for an item
    * @param $item
    * @return Query
    */
   public static function getAllLinkBetweenGlpiAndMantis(){
      global $DB;

      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.* FROM `glpi_plugin_mantis_mantis`");

   }

}
