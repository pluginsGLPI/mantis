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
      self::updateAttachment();
      return true;
   }

   static function cronInfo($name) {
      return array('description' => __("Update ticket", "mantis"));
   }


    /**
     * Function to check if for each glpi tickets linked , all of his Docuement exist in MantisBT
     * If not, the cron upload the documents to mantisBT
     */
    static function updateAttachment(){

        Toolbox::logInFile("mantis", "**************************************************");
        Toolbox::logInFile("mantis", "* CRON MANTIS : Starting update attachments cron *");
        Toolbox::logInFile("mantis", "**************************************************");

        global $DB;

        //on recuper l'id des ticket Glpi linké
        $res = self::getTicketWhichIsLinked();

        //on initialise la connection au webservice
        $ws = new PluginMantisMantisws();
        $ws->initializeConnection();

        //on parcours les ticket linké
        while ($row = $res->fetch_assoc()) {

            //on créer l'objet Ticket
            $ticket_glpi = new Ticket();
            $ticket_glpi->getFromDB($row['idTicket']);

            Toolbox::logInFile("mantis", "CRON MANTIS : Checking Glpi ticket ".$row['idTicket'].". ");

            //on recupere les lien entre le ticket glpi et les ticket mantis
            $list_link = self::getLinkBetweenTicketGlpiAndTicketMantis($row['idTicket']);

            //pour chaque lien glpi -> mantis
            while ($line = $list_link->fetch_assoc()){
                Toolbox::logInFile("mantis", "CRON MANTIS :    Checking Mantis ticket ".$line['idMantis'].". ");

                //on recupere l'issue mantisBT
                $issue = $ws->getIssueById($line['idMantis']);
                //ainsi que les document attaché
                $attachmentsMantisBT = $issue->attachments;

                //on recupere les document du tickets
                $documents = self::getdocumentFromTicket($row['idTicket']);

                //pour chaque document du ticket
                foreach($documents as $doc){

                    //on verifie s'il existe sur MantisBt
                    if(!self::existAttachmentInMantisBT($doc , $attachmentsMantisBT)){
                        //si le fichier n'existe pas on l'injecte
                        Toolbox::logInFile("mantis", "CRON MANTIS :      File ".$doc->getField('filename')." does not exist in MantisBT issue. ");
                        $path = GLPI_DOC_DIR . "/" . $doc->getField('filepath');

                        if (file_exists($path)) {

                            $data = file_get_contents($path);
                            if (!$data) {
                                Toolbox::logInFile("mantis","CRON MANTIS :      Can't load ".$doc->getField('filename').". ");
                            } else {

                                //on l'insere
                                //$data    = base64_encode($data);
                                $id_data = $ws->addAttachmentToIssue($line['idMantis'],
                                $doc->getField('filename'), $doc->getField('mime'), $data);

                                if (!$id_data) {
                                    $id_attachment[] = $id_data;
                                    Toolbox::logInFile("mantis", "CRON MANTIS :      Can't send ".$doc->getField('filename')." to MantisBT. ");
                                }else{
                                    Toolbox::logInFile("mantis", "CRON MANTIS :      Send ".$doc->getField('filename')." to MantisBT with success. ");
                                }

                            }
                        } else {

                          Toolbox::logInFile("mantis", "CRON MANTIS :      File ".$doc->getField('filename')." does not exist on Glpi server. ");

                        }

                    }else{

                    Toolbox::logInFile("mantis", "CRON MANTIS :      File ".$doc->getField('filename')." already exist in MantisBT issue. ");

                    }
                }
            }
        }
        Toolbox::logInFile("mantis", "************************************************");
        Toolbox::logInFile("mantis", "* CRON MANTIS : Ending update attachments cron *");
        Toolbox::logInFile("mantis", "************************************************");
   }


    /**
     * this function check the status of mantis issuelinked to a ticket
     * If status == status to close glpi ticket the the cron clos the ticket
     */
    static function updateTicket() {

        Toolbox::logInFile("mantis", "**************************************************");
        Toolbox::logInFile("mantis", "*   CRON MANTIS : Starting update tickets cron   *");
        Toolbox::logInFile("mantis", "**************************************************");

        $list_ticket_mantis = array();

        $conf = new PluginMantisConfig();
        $conf->getFromDB(1);

        if ($conf->getField('etatMantis')) {

            Toolbox::logInFile("mantis", "CRON MANTIS :Plugin configuration is correct. ");
            $etat_mantis = $conf->getField('etatMantis');
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();

            //on recuper l'id des ticket Glpi linké
            $res = self::getTicketWhichIsLinked();

            while ($row = $res->fetch_assoc()) {

                Toolbox::logInFile("mantis", "CRON MANTIS : Checking Glpi ticket ".$row['idTicket'].". ");

                $ticket_glpi = new Ticket();
                $ticket_glpi->getFromDB($row['idTicket']);

                //si le ticket est deja resolus ou clos
                if($ticket_glpi->fields['status'] == 5 || $ticket_glpi->fields['status'] == 6){

                    Toolbox::logInFile("mantis", "CRON MANTIS : Glpi ticket ".$row['idTicket']." is already solve or closed. ");

                }else{

                    //on recupere tout les tickets mantis lié
                    $list_link = self::getLinkBetweenTicketGlpiAndTicketMantis($row['idTicket']);

                    $list_ticket_mantis = array();
                    while ($line = $list_link->fetch_assoc()){
                        $mantis = $ws->getIssueById($line['idMantis']);
                        $list_ticket_mantis[] = $mantis;
                    }

                    Toolbox::logInFile("mantis", "CRON MANTIS : Checking all status from issue Mantis linked with glpi ticket");
                    if(self::getAllSameStatusChoiceByUser ($list_ticket_mantis, $etat_mantis) ){

                        Toolbox::logInFile("mantis", "CRON MANTIS : All status MantisBT are the same as status choice by user -> ".$etat_mantis.". ");

                        $info_solved = self::getInfoSolved($list_ticket_mantis);
                        $ticket_glpi->fields['status'] = Ticket::SOLVED;
                        $ticket_glpi->fields['closedate'] = date("Y-m-d");
                        $ticket_glpi->fields['solvedate'] = date("Y-m-d");
                        $ticket_glpi->fields['solution'] = $info_solved;
                        $ticket_glpi->update($ticket_glpi->fields);
                        Toolbox::logInFile("mantis", "CRON MANTIS : Update glpi ".$row['idTicket']." status in BDD");

                    }else{

                        Toolbox::logInFile("mantis", "CRON MANTIS : All status MantisBT have not the same as status choice by user -> ".$etat_mantis.". ");

                    }
                }
            }

            Toolbox::logInFile("mantis", "************************************************");
            Toolbox::logInFile("mantis", "*   CRON MANTIS : Ending update tickets cron   *");
            Toolbox::logInFile("mantis", "************************************************");

        } else {

            Toolbox::logInFile("mantis", "CRON MANTIS :Plugin configuration is not correct (status Mantis to resolve glpi ticket is missing). ");
            Toolbox::logInFile("mantis", "************************************************");
            Toolbox::logInFile("mantis", "*   CRON MANTIS : Ending update tickets cron   *");
            Toolbox::logInFile("mantis", "************************************************");

        }
   }


    /**
     * Function to check if $doc exist in MantisBT attachment
     * @param $doc
     * @param $attachmentsMantisBT
     * @return bool
     */
    static function existAttachmentInMantisBT($doc , $attachmentsMantisBT){

        foreach($attachmentsMantisBT as $attachment){
            if($attachment->filename == $doc->fields['filename']){
                return true;
                break;
            }
        }

        return false;
    }

    /**
     * Function to retrieve all document from ticket
     * @param $idticket
     * @return array
     */
    static function getdocumentFromTicket($idticket){

        global $DB;

        $document = array();
        $res = $DB->query("SELECT `glpi_documents_items`.*
                        FROM `glpi_documents_items` WHERE `glpi_documents_items`.`itemtype` = 'Ticket'
                        AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger($idticket) . "'");

        if ($res->num_rows > 0) {

            while ($row = $res->fetch_assoc()) {
                $doc = new Document();
                $doc->getFromDB($row["documents_id"]);
                $document[] = $doc;
            }

        }

        return $document;

    }


    /**
     * Function to extract issue from $list_tickket_mantis when they are the same status choice by user
     * @param $list_ticket_mantis
     * @param $status
     * @return bool
     */
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


          if($item->fields['status'] == $conf->fields['neutralize_escalation'] ||
              $item->fields['status'] > $conf->fields['neutralize_escalation']){

              $this->getFormForDisplayInfo($item);

          }else{

              // if canView or canWrite
              if (PluginMantisProfile::canViewMantis($_SESSION['glpiactiveprofile']['id'])
                  || PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id'])) {

                  $this->getFormForDisplayInfo($item);

                  // if canWrite
                  if (PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id'])) {
                      $this->displayBtnToLinkissueGlpi($item);
                  }
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
         $item->fields['id'], array('title'  => __("MantisBT actions", "mantis"),'width'  => 530,'height' => 400));

      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
         $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' .
         $item->fields['id'], array('title'  => __("MantisBT actions", "mantis"),'width'  => 620,'height' => 650));

      $content .= "<table id='table1'  class='tab_cadre_fixe' >";
      $content .= "<th colspan='6'>".__("MantisBT actions", "mantis")."</th>";
      $content .= "<tr class='tab_bg_1'>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisIssue.show();'  value='" .
         __('Link to an existing MantisBT ticket', 'mantis') ."' class='submit' style='width : 200px;'></td>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisProject.show();'  value='" .
         __('Create a new MantisBT ticket', 'mantis') ."' class='submit' style='width : 250px;'></td>";

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

       $conf = new PluginMantisConfig();
       $conf->getFromDB(1);

       $content = "<form action='#' id=".$id_link.">";
       $content .= "<table id=".$id_link." class='tab_cadre' cellpadding='5' >";
       $content .= "<th colspan='2'>".__("What do you do ?","mantis")."</th>";

       //CHECKBOX -> DEL LINK BETWEEN GLPI AND MANTIS
       $content .= "<tr class='tab_bg_1' >";
       $content .= "<td><INPUT type='checkbox'  id='deleteLink".$id_link."' >";
       $content .= __("Delete link of the MantisBT ticket","mantis")."</td>";
       $content .= "<td>".__("(Does not delete the MantisBT ticket)","mantis")."</td>";
       $content .= "</tr>";


       //show option to delete mantis issue or not (display:none)
       if($conf->fields['show_option_delete'] != 0){
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
       }else{
           $content .= "<tr class='tab_bg_1' style='display:none;'>";
           if (!$issue) {
               $content .= "<td><INPUT type='checkbox' disabled id='deleteIssue" . $id_link . "' >";
               $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
           } else {
               $content .= "<td><INPUT type='checkbox' id='deleteIssue" . $id_link . "' >";
               $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
           }
           $content .= "<td>".__("(Also removes the link in GLPI)","mantis")."</td>";
           $content .= "</tr>";
       }


       //sUBMIT BUTTON
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<td><input  id=" . $id_link . "  name='delo' value='" . __("Delete", "mantis") .
         "' class='submit' onclick='delLinkAndOrIssue(" .$id_link . "," . $id_mantis . "," . $id_ticket . ");'></td>";
       $content .= "<td><div id='infoDel" . $id_link . "' ></div>";
       $content .= "<img id='waitDelete" . $id_link . "' src='".$CFG_GLPI["root_doc"]."/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
       $content .= "</tr>";

       $content .= "<input type='hidden' name='idMantis".$id_link."' id='idMantis' value=" . $id_link . "       >";
       $content .= "<input type='hidden' name='id".$id_link."'       id='id'       value=" . $id_mantis . " >";
       $content .= "<input type='hidden' name='idTicket".$id_link."' id='idticket' value=" . $id_ticket . " >";

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

       //ID ISSUE MANTIS
       $content.= "<tr class='tab_bg_1'>";
       $content.= "<th width='100'>".__('Id Ticket','mantis')."</th>";
       $content.= "<td ><input size=35 id='idMantis' type='text' name='idMantis' onkeypress=\"if(event.keyCode==13)findProjectById();\" />".
       "<img id='searchImg' alt='rechercher' src='".$CFG_GLPI['root_doc']."/pics/aide.png'
        onclick='findProjectById();'style='cursor: pointer;padding-left:5px; padding-right:5px;'/></td>";
       $content.= "</tr>";

       //MANTIS FIELD FOR GLPI FIELDS
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)", "mantis")."</th><td>";
       $content .= Dropdown::showFromArray('fieldsGlpi1', array(), array('rand' => '' ,'display' => false));
       $content .= "</td></tr>";

       //MANTIS FIELD FOR GLPI FIELDS
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>". __("MantisBT field for the link to the ticket GLPI", "mantis")."</th><td>";
       $content .= Dropdown::showFromArray('fieldUrl1', array(), array('rand' => '' ,'display' => false));
       $content .= "</td></tr>";

       //FORWORD ATTACHMENT
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Attachments","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' >".
           __("To forward attachments","mantis")."</td></tr>";

       //FORWORD FOLLOW
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Glpi follow","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followFollow' id='followFollow' >".
           __("To forward follow","mantis")."</td></tr>";

       //FORWORD TASK
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Glpi task","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followTask' id='followTask' >".
           __("To forward task","mantis")."</td></tr>";

       //FORWORD TITLE
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Glpi title","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followTitle' id='followTitle' >".
           __("To forward title","mantis")."</td></tr>";

       //FORWORD DESCRIPTION
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Glpi description","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followDescription' id='followDescription' >".
           __("To forward description","mantis")."</td></tr>";

       //FORWORD CATEGORIE
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>".__("Glpi categorie","mantis")."</th>";
       $content .= "<td><INPUT type='checkbox' name='followCategorie' id='followCategorie' >".
           __("To forward categorie","mantis")."</td></tr>";

       //FOLLOW GLPI LINKED
       $content .= "<tr class='tab_bg_1'>";
       $content .= "<th>"._n('Linked ticket', 'Linked tickets', 2)."</th>";
       $content .= "<td><INPUT type='checkbox' name='linkedTicket1' id='linkedTicket1' >".
           __("To forward linked Ticket","mantis")."</td></tr>";

       //INPUT
       $content.= "<tr class='tab_bg_1'>";
       $content.= "<td><input  id='linktoIssue'  name='linktoIssue' value='Lier' class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";

       //INFO
       $content.= "<td width='150' height='20'>";
       $content.= "<div id='infoLinIssueGlpiToIssueMantis' ></div>";
       $content.= "<img id='waitForLinkIssueGlpiToIssueMantis'  src='".$CFG_GLPI['root_doc'].
         "/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
       $content.= "</tr>";

       //INPUT HIDDEN
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

        $config = new PluginMantisConfig();
        $config->getFromDB(1);

        $content  = "<form action='#' >";
        $content .= "<table id='table2' class='tab_cadre' cellpadding='5'>";
        $content .= "<tr class='headerRow'><th colspan='6'>".__("Create a new MantisBT ticket","mantis")."</th></tr>";

        //PROJECT NAME
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>Nom du projet</th>";
        $content .= "<td id='tdSearch' height='24'>";

        $content .= "<input  id='nameMantisProject' type='text'  name='resume'  onkeypress=\"if(event.keyCode==13)findProjectByName();\" />";
        $content .= "<img id='searchImg' alt='rechercher' src='".$CFG_GLPI['root_doc']."/pics/aide.png'
        onclick='findProjectByName();'style='cursor: pointer;padding-left:5px; padding-right:5px;'/></td>";
        $content .= "</tr>";

        //MANTIS CATEGORIE
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Category","mantis")."</th><td>";
        $content .= Dropdown::showFromArray('categorie', array(),array('rand' => '' ,'display' => false));
        $content .= "</td></tr>";

        //MANTIS FIELD FOR GLPI FIELDS
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)", "mantis")."</th><td>";
        $content .= Dropdown::showFromArray('fieldsGlpi', array(), array('rand' => '' ,'display' => false));
        $content .= "</td></tr>";

        //MANTIS FIELD FOR GLPI FIELDS
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>". __("MantisBT field for the link to the ticket GLPI", "mantis")."</th><td>";
        $content .= Dropdown::showFromArray('fieldUrl', array(), array('rand' => '' ,'display' => false));
        $content .= "</td></tr>";

        //MANTIS ASSIGNATION
        if($config->fields['enable_assign']){
            $content .= "<tr class='tab_bg_1'>";
            $content .= "<th>".__("Assignation","mantis")."</th><td>";
            $content .= Dropdown::showFromArray('assignation', array(),array('rand' => '' ,'display' => false));
            $content .= "</td></tr>";
        }

        //MANTIS SUMMARY
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Summary","mantis")."</th>";
        $content .= "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";

        //MANTIS DESCRIPTION
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Description","mantis")."</th>";
        $content .= "<td><textarea  rows='5' cols='55' name='description' id='description'></textarea></td></tr>";

        //MANTIS STEP TO REPRODUCE
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Steps to reproduce","mantis")."</th>";
        $content .= "<td><textarea  rows='5' cols='55' name='stepToReproduce' id='stepToReproduce'></textarea></td></tr>";

        //FOLLOW ATTACHMENT
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Attachments","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' >".
        __("To forward attachments","mantis")."</td></tr>";

        //FOLLOW GLPI FOLLOW
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Glpi follow","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followFollow' id='followFollow' >".
        __("To forward follow","mantis")."</td></tr>";

        //FOLLOW GLPI TASK
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Glpi task","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followTask' id='followTask' >".
        __("To forward task","mantis")."</td></tr>";

        //FOLLOW GLPI TITLE
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Glpi title","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followTitle' id='followTitle' >".
        __("To forward title","mantis")."</td></tr>";

        //FOLLOW GLPI DEXCRIPTION
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Glpi description","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followDescription' id='followDescription' >".
        __("To forward description","mantis")."</td></tr>";

        //FOLLOW GLPI CATEGORIE
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>".__("Glpi categorie","mantis")."</th>";
        $content .= "<td><INPUT type='checkbox' name='followCategorie' id='followCategorie' >".
            __("To forward categorie","mantis")."</td></tr>";

        //FOLLOW GLPI LINKED
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<th>"._n('Linked ticket', 'Linked tickets', 2)."</th>";
        $content .= "<td><INPUT type='checkbox' name='linkedTicket' id='linkedTicket' >".
            __("To forward linked Ticket","mantis")."</td></tr>";

        //INPUT HIDDEN
        $content .= "<tr class='tab_bg_1'>";
        $content .= "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=". $id_ticket . " class='submit'>";
        $content .= "<input type='hidden' class='center' name='user' id='user' value=" .Session::getLoginUserID() . " class='submit'>";
        $content .= "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=" .date("Y-m-d") . " class='submit'>";

        //INPUT BUTTON
        $content .= "<input  id='linktoProject' onclick='linkIssueglpiToProjectMantis();'name='linktoProject' value='".__("Link","mantis")."' class='submit'></td>";

        //DIV INVO FOR CALL AJAX ERROR
        $content .= "<td width='150' >";
        $content .= "<div id='infoLinkIssueGlpiToProjectMantis' ></div>";
        $content .= "<img id='waitForLinkIssueGlpiToProjectMantis' src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/please_wait.gif' style='display:none;'/>";
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

       $conf = new PluginMantisConfig();
       $conf->getFromDB(1);

       if($item->fields['status'] == $conf->fields['neutralize_escalation'] ||
           $item->fields['status'] > $conf->fields['neutralize_escalation']){
           $can_write = false;
       }else{
           $can_write = PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id']);
       }


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

             //just change height of popup
             if($conf->fields['show_option_delete'] != 0){
                 Ajax::createModalWindow('popupToDelete' . $row['id'],
                     $CFG_GLPI['root_doc'].'/plugins/mantis/front/mantis.form.php?action=deleteIssue&id=' .
                     $row['id'] . '&idTicket=' . $row['idTicket'] . '&idMantis=' . $row['idMantis'],
                     array('title'  => __("Delete", "mantis"),
                         'width'  => 550,
                         'height' => 160));
             }else{
                 Ajax::createModalWindow('popupToDelete' . $row['id'],
                     $CFG_GLPI['root_doc'].'/plugins/mantis/front/mantis.form.php?action=deleteIssue&id=' .
                     $row['id'] . '&idTicket=' . $row['idTicket'] . '&idMantis=' . $row['idMantis'],
                     array('title'  => __("Delete", "mantis"),
                         'width'  => 550,
                         'height' => 110));
             }


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

               $content .= "<tr>";
               $content .= "<td class='center'>";
               $content .= "<a href='".$conf->fields['host']."/view.php?id=" . $issue->id . "' target='_blank' >";
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
