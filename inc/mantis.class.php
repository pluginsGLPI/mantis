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
 * Class PluginMantis -> class générale du plugin Mantis
 */
class PluginMantisMantis extends CommonDBTM {


   static function getTypeName($nb = 0) {

      return __('MantisBT', 'mantis');
   }

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Ticket' || $item->getType()=='Problem') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(), self::countForItem($item));
         }
         return self::getTypeName();
      }
      return '';
   }

   /**
    * @see CommonGLPI::displayTabContentForItem()
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Ticket' || $item->getType()=='Problem') {
         if (Session::haveRightsOr('plugin_mantis_use', array(READ, UPDATE))) {
            $PluginMantisMantis = new self();
            $PluginMantisMantis->showForm($item);
         } else {
            echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] .
                     "/pics/warning.png\" alt=\"warning\"><br><br>";
            echo "<b>" . __("Access denied") . "</b></div>";
         }

      }
   }

   /**
    * @param $item    CommonDBTM object
   **/
   public static function countForItem(CommonDBTM $item) {
      return countElementsInTable(getTableForItemType(__CLASS__), 
                                    "`items_id` = '".$item->getID()."'");
   }

   /**
    * Install this class in GLPI
    * 
    * 
    */
   static function install($migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);

      if (!TableExists($table)) {

         $query = "CREATE TABLE `".$table."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `items_id` int(11) NOT NULL,
                     `idMantis` int(11) NOT NULL,
                     `dateEscalade` date NOT NULL,
                     `itemtype` varchar(255) NOT NULL,
                     `user` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());

      }else{
         /* TODO
         $migration->addField($table, 'itemType', 'string');
         $migration->executeMigration();

         $migration->addField($table, 'itemType', 'string');
         $migration->changeField('glpi_plugin_mantis_mantis','itemType','itemtype','string' ,array());
         $migration->changeField('glpi_plugin_mantis_mantis','idTicket','items_id','integer' ,array());
         $migration->executeMigration();
         */
      }
      
      //Create CLI automated task
      $cron = new CronTask();
      if (! $cron->getFromDBbyName(__CLASS__, 'mantis')) {
         CronTask::Register(__CLASS__, 'mantis', 7 * DAY_TIMESTAMP, array(
               'param' => 24,
               'mode' => CronTask::MODE_EXTERNAL
         ));
      }
   }
   
   /**
    * Uninstall Cron Task from BDD
    */
   static function uninstall() {
      global $DB;
      
      CronTask::Unregister(__CLASS__);
      
      $query = "DROP TABLE IF EXISTS `glpi_plugin_mantis_mantis`";
      $DB->query($query) or die($DB->error());
   }

   /**
    * Task Execution
    *
    * @param $task
    * @return bool
    */
   static function cronMantis($task) {
      self::updateTicket();
      self::updateAttachment();
      return true;
   }

   /**
    * Name and Info of Cron Task
    *
    * @param $name
    * @return array
    */
   static function cronInfo($name) {
      return array(
            'description' => __("Update ticket", "mantis")
      );
   }

   /**
    * Function to check if for each glpi tickets linked , all of his Docuement exist in MantisBT
    * If not, the cron upload the documents to mantisBT
    */
   static function updateAttachment() {
      Toolbox::logInFile("mantis", "**************************************************");
      Toolbox::logInFile("mantis", "* CRON MANTIS : Starting update attachments cron *");
      Toolbox::logInFile("mantis", "**************************************************");
      
      global $DB;
      
      // on recuper l'id des item Glpi linké
      $res = self::getItemWhichIsLinked();
      
      // on initialise la connection au webservice
      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();
      
      // on parcours les items linké
      while ( $row = $res->fetch_assoc() ) {
         
         $itemType = $row['itemtype'];
         
         // on créer l'objet Ticket
         $item = new $itemType();
         $item->getFromDB($row['items_id']);
         
         if ($item->fields['status'] == 5 || $item->fields['status'] == 6) {
            Toolbox::logInFile("mantis", "CRON MANTIS : " . $itemType . " " . $itemType . " " . $row['items_id'] . " is already solve or closed. ");
         } else {
            Toolbox::logInFile("mantis", "CRON MANTIS : Checking " . $itemType . " ticket " . $row['items_id'] . ". ");
            
            // on recupere les lien entre le ticket glpi et les ticket mantis
            $list_link = self::getLinkBetweenItemGlpiAndTicketMantis($row['items_id'], $itemType);
            
            // pour chaque lien glpi -> mantis
            while ( $line = $list_link->fetch_assoc() ) {
               Toolbox::logInFile("mantis", "CRON MANTIS :    Checking Mantis ticket " . $line['idMantis'] . ". ");
               
               // on recupere l'issue mantisBT et les pièce jointes
               $issue = $ws->getIssueById($line['idMantis']);
               $attachmentsMantisBT = $issue->attachments;
               
               // on recupere les document de l'item
               $documents = self::getDocumentFromItem($row['items_id'], $itemType);
               
               // pour chaque document
               foreach ($documents as $doc) {
                  // on verifie s'il existe sur MantisBt
                  if (! self::existAttachmentInMantisBT($doc, $attachmentsMantisBT)) {
                     // si le fichier n'existe pas on l'injecte
                     Toolbox::logInFile("mantis", "CRON MANTIS :      File " . $doc->getField('filename') . " does not exist in MantisBT issue. ");
                     $path = GLPI_DOC_DIR . "/" . $doc->getField('filepath');
                     if (file_exists($path)) {
                        $data = file_get_contents($path);
                        if (! $data) {
                           Toolbox::logInFile("mantis", "CRON MANTIS :      Can't load " . $doc->getField('filename') . ". ");
                        } else {
                           // on l'insere
                           $id_data = $ws->addAttachmentToIssue($line['idMantis'], $doc->getField('filename'), $doc->getField('mime'), $data);
                           
                           if (! $id_data) {
                              $id_attachment[] = $id_data;
                              Toolbox::logInFile("mantis", "CRON MANTIS :      Can't send " . $doc->getField('filename') . " to MantisBT. ");
                           } else {
                              Toolbox::logInFile("mantis", "CRON MANTIS :      Send " . $doc->getField('filename') . " to MantisBT with success. ");
                           }
                        }
                     } else {
                        Toolbox::logInFile("mantis", "CRON MANTIS :      File " . $doc->getField('filename') . " does not exist on Glpi server. ");
                     }
                  } else {
                     Toolbox::logInFile("mantis", "CRON MANTIS :      File " . $doc->getField('filename') . " already exist in MantisBT issue. ");
                  }
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
      
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);
      
      if ($conf->getField('etatMantis')) {
         Toolbox::logInFile("mantis", "CRON MANTIS :Plugin configuration is correct. ");
         
         $etat_mantis = $conf->getField('etatMantis');
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();
         
         // on recuper l'id des items Glpi linké
         $res = self::getItemWhichIsLinked();
         
         while ( $row = $res->fetch_assoc() ) {
            
            $itemType = $row['itemtype'];
            $item = new $itemType();
            $item->getFromDB($row['items_id']);
            
            Toolbox::logInFile("mantis", "CRON MANTIS : Checking Glpi " . $itemType . " " . $row['items_id'] . ". ");
            
            // si le ticket est deja resolus ou clos
            if ($item->fields['status'] == 5 || $item->fields['status'] == 6) {
               
               Toolbox::logInFile("mantis", "CRON MANTIS : Glpi " . $itemType . " " . $row['items_id'] . " is already solve or closed. ");
            } else {
               
               // on recupere tout les tickets mantis lié
               $list_link = self::getLinkBetweenItemGlpiAndTicketMantis($row['items_id'], $itemType);
               
               $list_ticket_mantis = array();
               while ( $line = $list_link->fetch_assoc() ) {
                  $mantis = $ws->getIssueById($line['idMantis']);
                  $list_ticket_mantis[] = $mantis;
               }
               
               Toolbox::logInFile("mantis", "CRON MANTIS : Checking all status from issue Mantis linked with glpi " . $itemType);
               
               if (self::getAllSameStatusChoiceByUser($list_ticket_mantis, $etat_mantis)) {
                  
                  Toolbox::logInFile("mantis", "CRON MANTIS : All status MantisBT are the same as status choice by user -> " . $etat_mantis . ". ");
                  $info_solved = self::getInfoSolved($list_ticket_mantis);
                  $item->fields['status'] = $itemType::SOLVED;
                  $item->fields['closedate'] = date("Y-m-d");
                  $item->fields['solvedate'] = date("Y-m-d");
                  $item->fields['solution'] = $info_solved;
                  $item->update($item->fields);
                  Toolbox::logInFile("mantis", "CRON MANTIS : Update glpi " . $row['items_id'] . " status in BDD");
               } else {
                  Toolbox::logInFile("mantis", "CRON MANTIS : All status MantisBT have not the same as status choice by user -> " . $etat_mantis . ". ");
               }
            }
         }
         
         Toolbox::logInFile("mantis", "************************************************");
         Toolbox::logInFile("mantis", "*   CRON MANTIS : Ending update tickets cron   *");
         Toolbox::logInFile("mantis", "************************************************");
      } else {
         
         Toolbox::logInFile("mantis", "CRON MANTIS : Plugin configuration is not correct (status Mantis to resolve glpi ticket is missing). ");
         Toolbox::logInFile("mantis", "************************************************");
         Toolbox::logInFile("mantis", "*   CRON MANTIS : Ending update tickets cron   *");
         Toolbox::logInFile("mantis", "************************************************");
      }
   }

   /**
    * Function to check if $doc exist in MantisBT attachment
    *
    * @param $doc
    * @param $attachmentsMantisBT
    * @return bool
    */
   static function existAttachmentInMantisBT($doc, $attachmentsMantisBT) {
      foreach ($attachmentsMantisBT as $attachment) {
         if ($attachment->filename == $doc->fields['filename']) {
            return true;
            break;
         }
      }
      
      return false;
   }

   /**
    * Function to retrieve all document from ticket
    *
    * @param $idItem
    * @param $itemType
    * @return array
    */
   static function getDocumentFromItem($idItem, $itemType) {
      global $DB;
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);
      
      $document = array();
      
      if ($conf->fields['doc_categorie'] == 0) {
         $res = $DB->query("SELECT `glpi_documents_items`.*
            FROM `glpi_documents_items`
            WHERE `glpi_documents_items`.`itemtype` = '" . $itemType . "'
            AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger($idItem) . "'");
      } else {
         $res = $DB->query("SELECT `glpi_documents_items`.*
            FROM `glpi_documents_items` ,`glpi_documents`
            WHERE `glpi_documents`.`id` =`glpi_documents_items`.`documents_id`
            AND `glpi_documents`.`documentcategories_id` = '" . Toolbox::cleanInteger($conf->fields['doc_categorie']) . "'
            AND `glpi_documents_items`.`itemtype`  = '" . $itemType . "'
            AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger($idItem) . "'");
      }
      
      while ( $row = $res->fetch_assoc() ) {
         $doc = new Document();
         $doc->getFromDB($row["documents_id"]);
         $document[] = $doc;
      }
      
      return $document;
   }

   /**
    * Function to extract issue from $list_tickket_mantis when they are the same status choice by user
    *
    * @param $list_ticket_mantis
    * @param $status
    * @return bool
    */
   private static function getAllSameStatusChoiceByUser($list_ticket_mantis, $status) {
      $diferrent = false;
      if (count($list_ticket_mantis) == 0)
         return false;
      for($i = 0; $i <= count($list_ticket_mantis); $i ++) {
         Toolbox::logInFile("mantis", "CRON MANTIS :      Check status for Item " . $list_ticket_mantis[$i]->id . " ->" . $list_ticket_mantis[$i]->status->name);
         if ($list_ticket_mantis[$i]->status->name != $status)
            $diferrent = true;
         break;
      }
      if ($diferrent)
         return false;
      else
         return true;
   }

   /**
    * function to get id ticket Glpi which is linked
    *
    * @return Query
    */
   private static function getItemWhichIsLinked() {
      global $DB;
      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.`items_id`,`glpi_plugin_mantis_mantis`.`itemtype`
                            FROM `glpi_plugin_mantis_mantis`
                            GROUP BY `glpi_plugin_mantis_mantis`.`items_id`,`glpi_plugin_mantis_mantis`.`itemtype`");
   }

   /**
    * Function to get link between glpi ticket and mantisBT ticket for an glpi ticket
    *
    * @param $idItem
    * @param $itemType
    * @return Query
    */
   private static function getLinkBetweenItemGlpiAndTicketMantis($idItem, $itemType) {
      global $DB;
      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`items_id` = '" . Toolbox::cleanInteger($idItem) . "' and `glpi_plugin_mantis_mantis`.`itemtype` = '" . $itemType . "'");
   }

   /**
    * Function to get information in Note for each ticket MAntisBT
    *
    * @param $list_ticket_mantis
    * @return string
    */
   private static function getInfoSolved($list_ticket_mantis) {
      $info = "";
      foreach ($list_ticket_mantis as &$ticket) {
         $notes = $ticket->notes;
         foreach ($notes as &$note) {
            $info .= $ticket->id . " - " . $note->reporter->name . " : " . $note->text . "<br/>";
         }
      }
      return $info;
   }

   static function canCreate() {
      return Session::haveRight('plugin_mantis_use', UPDATE);
   }

   static function canView() {
      return Session::haveRight('plugin_mantis_use', READ);
   }

   /**
    * Function to show the form of plugin
    *
    * @param $item
    */
   public function showForm($item) {
      global $CFG_GLPI;
      $ws = new PluginMantisMantisws();
      $conf = new PluginMantisConfig();
      // recover the first and only record
      $conf->getFromDB(1);
      
      // check if Web Service Mantis works fine
      if ($ws->testConnectionWS($conf->getField('host'), 
                                $conf->getField('url'), 
                                $conf->getField('login'), 
                                Toolbox::decrypt($conf->getField('pwd'), GLPIKEY))) {

         if ($item->fields['status'] == $conf->fields['neutralize_escalation'] 
               || $item->fields['status'] > $conf->fields['neutralize_escalation']) {
            $this->getFormForDisplayInfo($item, $item->getType());
         } else {
            // if canView or canWrite
            if (Session::haveRightsOr('plugin_mantis_use', array(READ, UPDATE))) {
               $this->getFormForDisplayInfo($item, $item->getType());
               // if canWrite
               if (Session::haveRight('plugin_mantis_use', UPDATE)) {
                  $this->displayBtnToLinkissueGlpi($item);
               }
            }
         }
      } else {
         $content = "<div class='center'>";
         $content .= "<img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png'  alt='warning'>";
         $content .= "<b>" . __("Thank you configure the mantis plugin", "mantis") . "</b>";
         $content .= "</div>";
         echo $content;
      }
   }

   /**
    * function to show action give by plugin
    *
    * @param $item
    */
   public function displayBtnToLinkissueGlpi($item) {
      global $CFG_GLPI;
      
      $content = "<div id='popupLinkGlpiIssuetoMantisIssue' ></div>";
      $content .= "<div id='popupLinkGlpiIssuetoMantisProject' ></div>";
      
      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisIssue', $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToIssue&idTicket=' . $item->fields['id'] . '&itemType=' . $item->getType(), array(
            'title' => __("MantisBT actions", "mantis"),
            'width' => 530,
            'height' => 400
      ));
      
      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject', $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' . $item->fields['id'] . '&itemType=' . $item->getType(), array(
            'title' => __("MantisBT actions", "mantis"),
            'width' => 620,
            'height' => 650
      ));
      
      $content .= "<table id='table1'  class='tab_cadre_fixe' >";
      $content .= "<th colspan='6'>" . __("MantisBT actions", "mantis") . "</th>";
      $content .= "<tr class='tab_bg_1'>";
      
      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisIssue.dialog(\"open\");'  value='" . __('Link to an existing MantisBT ticket', 'mantis') . "' class='submit' style='width : 200px;'></td>";
      
      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisProject.dialog(\"open\");'  value='" . __('Create a new MantisBT ticket', 'mantis') . "' class='submit' style='width : 250px;'></td>";
      
      $content .= "</tr>";
      $content .= "</table>";
      
      echo $content;
   }

   /**
    * Form for delete Link Between Glpi ticket and MantisBT ticket or MantisBT ticket
    *
    * @param $id_link
    * @param $id_Item
    * @param $id_mantis
    * @param $itemType
    */
   public function getFormToDelLinkOrIssue($id_link, $id_Item, $id_mantis, $itemType) {
      global $CFG_GLPI;
      
      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();
      $issue = $ws->getIssueById($id_mantis);
      
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);
      
      $style = "";
      $disabled = "";
      
      // show option to delete mantis issue or not (display:none)
      if ($conf->fields['show_option_delete'] == 0)
         $style = "style='display:none;'";
         // disabled "delete mantisBT issue if issue not exist"
      if (! $issue)
         $disabled = "disabled";
      
      $content = "<form action='#' id=" . $id_link . ">";
      $content .= "<table id=" . $id_link . " class='tab_cadre' cellpadding='5' >";
      $content .= "<th colspan='2'>" . __("What do you do ?", "mantis") . "</th>";
      
      // CHECKBOX -> DEL LINK BETWEEN GLPI AND MANTIS
      $content .= "<tr class='tab_bg_1' >";
      $content .= "<td><INPUT type='checkbox'  id='deleteLink" . $id_link . "' >";
      $content .= __("Delete link of the MantisBT ticket", "mantis") . "</td>";
      $content .= "<td>" . __("(Does not delete the MantisBT ticket)", "mantis") . "</td>";
      $content .= "</tr>";
      
      // CHECKBOX -> DEL MANTISBT ISSUE
      $content .= "<tr class='tab_bg_1' " . $style . ">";
      $content .= "<td><INPUT type='checkbox' " . $disabled . " id='deleteIssue" . $id_link . "' >";
      $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
      $content .= "<td>" . __("(Also removes the link in GLPI)", "mantis") . "</td>";
      $content .= "</tr>";
      
      // SUBMIT BUTTON
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input  id=" . $id_link . "  name='delo' value='" . __("Delete", "mantis") . "' class='submit' onclick='delLinkAndOrIssue(" . $id_link . "," . $id_mantis . "," . $id_Item . ");'></td>";
      $content .= "<td><div id='infoDel" . $id_link . "' ></div>";
      $content .= "<img id='waitDelete" . $id_link . "' src='" . $CFG_GLPI["root_doc"] . "/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      $content .= "</tr>";
      
      // INPUT HIDDEN
      $content .= "<input type='hidden' name='idMantis" . $id_link . "' id='idMantis' value=" . $id_link . "       >";
      $content .= "<input type='hidden' name='id" . $id_link . "'       id='id'       value=" . $id_mantis . " >";
      $content .= "<input type='hidden' name='idTicket" . $id_link . "' id='idticket' value=" . $id_Item . " >";
      $content .= "<input type='hidden' name='itemType" . $id_link . "' id='itemType' value=" . $itemType . " class='submit'>";
      
      $content .= "</table>";
      $content .= Html::closeForm(false);
      
      echo $content;
   }

   /**
    * Form to link glpi ticket to Mantis Ticket
    *
    * @param $item
    * @param $itemType
    */
   public function getFormForLinkGlpiTicketToMantisTicket($item, $itemType) {
      global $CFG_GLPI;
      
      $pref = new PluginMantisUserpref();
      if (!$pref->getFromDB(Session::getLoginUserID())) {
         $pref->getEmpty();
         $pref->fields['users_id'] = Session::getLoginUserID();
         $pref->fields['id'] = Session::getLoginUserID();
         $pref->add($pref->fields);
         $pref->updateInDB($pref->fields);
      }
      
      $style = "";
      if ($itemType == 'Problem')
         $style = "style='display:none;'";
      
      $content = "<form action='#' >";
      $content .= "<table class='tab_cadre'cellpadding='5'>";
      $content .= "<th colspan='6'>" . __('Link to an existing MantisBT ticket', 'mantis') . "</th>";
      
      // ID ISSUE MANTIS
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th width='100'>" . __('Id Ticket', 'mantis') . "</th>";
      $content .= "<td ><input size=35 id='idMantis1' type='text' name='idMantis1' onkeypress=\"if(event.keyCode==13)findProjectById();\" />" . "<img id='searchImg' alt='rechercher' src='" . $CFG_GLPI['root_doc'] . "/pics/aide.png'
        onclick='findProjectById();'style='cursor: pointer;padding-left:5px; padding-right:5px;'/></td>";
      $content .= "</tr>";
      
      // MANTIS FIELD FOR GLPI FIELDS
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('fieldsGlpi1', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // MANTIS FIELD FOR GLPI FIELDS
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("MantisBT field for the link to the ticket GLPI", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('fieldUrl1', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // FORWORD ATTACHMENT
      $checked = ($pref->fields['followAttachment']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Attachments", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followAttachment1'  onclick='getAttachment1();' id='followAttachment1' " . $checked . " >" . __("To forward attachments", "mantis") . "<div id='attachmentforLinkToProject1' ><div/></td></tr>";
      
      // FORWORD FOLLOW
      $checked = ($pref->fields['followFollow'] && $style == "") ? "checked" : "";
      $content .= "<tr class='tab_bg_1' " . $style . ">";
      $content .= "<th>" . __("Glpi follow", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followFollow1' id='followFollow1' " . $checked . ">" . __("To forward follow", "mantis") . "</td></tr>";
      
      // FORWORD TASK
      $checked = ($pref->fields['followTask']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi task", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followTask1' id='followTask1'  " . $checked . ">" . __("To forward task", "mantis") . "</td></tr>";
      
      // FORWORD TITLE
      $checked = ($pref->fields['followTitle']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi title", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followTitle1' id='followTitle1' " . $checked . ">" . __("To forward title", "mantis") . "</td></tr>";
      
      // FORWORD DESCRIPTION
      $checked = ($pref->fields['followDescription']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi description", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followDescription1' id='followDescription1' " . $checked . ">" . __("To forward description", "mantis") . "</td></tr>";
      
      // FORWORD CATEGORIE
      $checked = ($pref->fields['followCategorie']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi categorie", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followCategorie1' id='followCategorie1' " . $checked . ">" . __("To forward categorie", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI LINKED
      $checked = ($pref->fields['followLinkedItem'] && $style == "") ? "checked" : "";
      $content .= "<tr class='tab_bg_1' " . $style . ">";
      $content .= "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
      $content .= "<td><INPUT type='checkbox' name='linkedTicket1' id='linkedTicket1' " . $checked . ">" . __("To forward linked Ticket", "mantis") . "</td></tr>";
      
      // INPUT
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input  id='linktoIssue'  name='linktoIssue' value='Lier' class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";
      
      // INFO
      $content .= "<td width='150' height='20'>";
      $content .= "<div id='infoLinIssueGlpiToIssueMantis' ></div>";
      $content .= "<img id='waitForLinkIssueGlpiToIssueMantis'  src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      $content .= "</tr>";
      
      // INPUT HIDDEN
      $content .= "<input type='hidden' name='idTicket1' id='idTicket1' value=" . $item . " >";
      $content .= "<input type='hidden' name='user1' id='user1' value=" . Session::getLoginUserID() . " >";
      $content .= "<input type='hidden' name='dateEscalade1' id='dateEscalade1' value=" . date("Y-m-d") . " >";
      $content .= "<input type='hidden' class='center' name='itemType1' id='itemType1' value=" . $itemType . " class='submit'>";
      
      $content .= "</table>";
      $content .= Html::closeForm(false);
      
      echo $content;
   }

   /**
    * Form to link glpi ticket to mantis project
    *
    * @param $idItem
    * @param $itemType
    */
   public function getFormForLinkGlpiTicketToMantisProject($idItem, $itemType) {
      global $CFG_GLPI;
      
      $config = new PluginMantisConfig();
      $config->getFromDB(1);
      
      $pref = new PluginMantisUserpref();
      if (!$pref->getFromDB(Session::getLoginUserID())) {
         $pref->getEmpty();
         $pref->fields['users_id'] = Session::getLoginUserID();
         $pref->fields['id'] = Session::getLoginUserID();
         $pref->add($pref->fields);
         $pref->updateInDB($pref->fields);
      }
      
      $styleItemType = "";
      if ($itemType == 'Problem')
         $styleItemType = "style='display:none;'";
      
      $styleAssignation = "";
      if (! $config->fields['enable_assign'])
         $styleAssignation = "style='display:none;'";
      
      $content = "<form action='#' >";
      $content .= "<table id='table2' class='tab_cadre' cellpadding='5'>";
      $content .= "<tr class='headerRow'><th colspan='6'>" . __("Create a new MantisBT ticket", "mantis") . "</th></tr>";
      
      // PROJECT NAME
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>Nom du projet</th>";
      $content .= "<td id='tdSearch' height='24'>";
      $content .= "<input  id='nameMantisProject' type='text'  name='resume'  onkeypress=\"if(event.keyCode==13)findProjectByName();\" />";
      $content .= "<img id='searchImg' alt='rechercher' src='" . $CFG_GLPI['root_doc'] . "/pics/aide.png'
        onclick='findProjectByName();'style='cursor: pointer;padding-left:5px; padding-right:5px;'/></td>";
      $content .= "</tr>";
      
      // MANTIS CATEGORIE
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Category", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('categorie', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // MANTIS FIELD FOR GLPI FIELDS
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("MantisBT field for GLPI fields<br/> (title, description, category, follow-up, tasks)", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('fieldsGlpi', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // MANTIS FIELD FOR GLPI FIELDS
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("MantisBT field for the link to the ticket GLPI", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('fieldUrl', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // MANTIS ASSIGNATION
      $content .= "<tr class='tab_bg_1' " . $styleAssignation . ">";
      $content .= "<th>" . __("Assignation", "mantis") . "</th><td>";
      $content .= Dropdown::showFromArray('assignation', array(), array(
            'rand' => '',
            'display' => false
      ));
      $content .= "</td></tr>";
      
      // MANTIS SUMMARY
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Summary", "mantis") . "</th>";
      $content .= "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";
      
      // MANTIS DESCRIPTION
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Description", "mantis") . "</th>";
      $content .= "<td><textarea  rows='5' cols='55' name='description' id='description'></textarea></td></tr>";
      
      // MANTIS STEP TO REPRODUCE
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Steps to reproduce", "mantis") . "</th>";
      $content .= "<td><textarea  rows='5' cols='55' name='stepToReproduce' id='stepToReproduce'></textarea></td></tr>";
      
      // FOLLOW ATTACHMENT
      $checked = ($pref->fields['followAttachment']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Attachments", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' onclick='getAttachment();'style='cursor: pointer;' " . $checked . ">" . __("To forward attachments", "mantis") . "<div id='attachmentforLinkToProject' ><div/></td></tr>";
      
      // FOLLOW GLPI FOLLOW
      $checked = ($pref->fields['followFollow'] && $styleItemType == "") ? "checked" : "";
      $content .= "<tr class='tab_bg_1' " . $styleItemType . ">";
      $content .= "<th>" . __("Glpi follow", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followFollow' id='followFollow' " . $checked . ">" . __("To forward follow", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI TASK
      $checked = ($pref->fields['followTask']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi task", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followTask' id='followTask' " . $checked . " >" . __("To forward task", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI TITLE
      $checked = ($pref->fields['followTitle']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi title", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followTitle' id='followTitle' " . $checked . " >" . __("To forward title", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI DEXCRIPTION
      $checked = ($pref->fields['followDescription']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi description", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followDescription' id='followDescription' " . $checked . " >" . __("To forward description", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI CATEGORIE
      $checked = ($pref->fields['followCategorie']) ? "checked" : "";
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>" . __("Glpi categorie", "mantis") . "</th>";
      $content .= "<td><INPUT type='checkbox' name='followCategorie' id='followCategorie' " . $checked . ">" . __("To forward categorie", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI LINKED
      $checked = ($pref->fields['followLinkedItem'] && $styleItemType == "") ? "checked" : "";
      $content .= "<tr class='tab_bg_1' " . $styleItemType . ">";
      $content .= "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
      $content .= "<td><INPUT type='checkbox' name='linkedTicket' id='linkedTicket' " . $checked . ">" . __("To forward linked Ticket", "mantis") . "</td></tr>";
      
      // INPUT HIDDEN
      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=" . $idItem . " class='submit'>";
      $content .= "<input type='hidden' class='center' name='user' id='user' value=" . Session::getLoginUserID() . " class='submit'>";
      $content .= "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=" . date("Y-m-d") . " class='submit'>";
      $content .= "<input type='hidden' class='center' name='itemType' id='itemType' value=" . $itemType . " class='submit'>";
      
      // INPUT BUTTON
      $content .= "<input  id='linktoProject' onclick='linkIssueglpiToProjectMantis();'name='linktoProject' value='" . __("Link", "mantis") . "' class='submit'></td>";
      
      // DIV INVO FOR CALL AJAX ERROR
      $content .= "<td width='150' >";
      $content .= "<div id='infoLinkIssueGlpiToProjectMantis' ></div>";
      $content .= "<img id='waitForLinkIssueGlpiToProjectMantis' src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/please_wait.gif' style='display:none;'/>";
      $content .= "</td>";
      
      $content .= "</table>";
      $content .= Html::closeForm(false);
      
      echo $content;
   }

   /**
    * Form to display information from MantisBT
    *
    * @param $item
    * @param $itemType
    */
   private function getFormForDisplayInfo($item, $itemType) {
      global $CFG_GLPI;
      
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);
      
      if ($item->fields['status'] == $conf->fields['neutralize_escalation'] || $item->fields['status'] > $conf->fields['neutralize_escalation']) {
         $can_write = false;
      } else {
         $can_write = Session::haveRight('plugin_mantis_use', UPDATE);
      }
      
      $content = "";
      
      // on recupere l'ensemble des lien entre ticket glpi et ticket(s) mantis
      $res = $this->getLinkBetweenGlpiAndMantis($item, $itemType);
      
      if ($res->num_rows > 0) {
         
         $content .= "<table id='table1'  class='tab_cadre_fixe' >";
         $content .= "<th colspan='8'>" . __("Already MantisBT tickets linked", "mantis") . "</th>";
         
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
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();
         
         while ( $row = $res->fetch_assoc() ) {
            $user->getFromDB($row["user"]);
            $issue = $ws->getIssueById($row["idMantis"]);
            $conf->getFromDB(1);
            
            $content .= '<div id=\'popupToDelete' . $row['id'] . '\'></div>';
            
            if ($conf->fields['show_option_delete'] != 0) {
               $height = 550;
            } else {
               $height = 200;
            }
            
            Ajax::createModalWindow('popupToDelete' . $row['id'], $CFG_GLPI['root_doc'] . '/plugins/mantis/front/mantis.form.php?action=deleteIssue&id=' . $row['id'] . '&idTicket=' . $row['items_id'] . '&idMantis=' . $row['idMantis'] . '&itemType=' . $itemType, array(
                  'title' => __("Delete", "mantis"),
                  'width' => 550,
                  'height' => $height
            ));
            
            if (! $issue) {
               $content .= "<tr>";
               $content .= "<td class='center'><img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/cross16.png'/></td>";
               $content .= "<td>" . $row["idMantis"] . "</td>";
               
               if ($can_write) {
                  $content .= "<td class='center' colspan='5'>" . __('This ticket does not in the  MantisBT database', 'mantis') . "</td>";
                  $content .= "<td class = 'center'> <img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/bin16.png'  onclick='popupToDelete" . $row['id'] . ".dialog(\"open\")';   style='cursor: pointer;' title=" . __("Delete link", "mantis") . "/></td>";
                  $content .= "</tr>";
               } else {
                  $content .= "<td colspan='6' class='center'>" . __('This ticket does not in the  MantisBT database', 'mantis') . "</td>";
                  $content .= "</tr>";
               }
            } else {
               $content .= "<tr>";
               $content .= "<td class='center'>";
               $content .= "<a href='" . $conf->fields['host'] . "/view.php?id=" . $issue->id . "' target='_blank' >";
               $content .= "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/arrowRight16.png'/>";
               $content .= "</a></td>";
               $content .= "<td class='center'>" . $issue->id . "</td>";
               $content .= "<td class='center'>" . stripslashes($issue->summary) . "</td>";
               $content .= "<td class='center'>" . $issue->project->name . "</td>";
               $content .= "<td class='center'>" . $issue->status->name . "</td>";
               $content .= "<td class='center'>" . $row["dateEscalade"] . "</td>";
               $content .= "<td class='center'>" . $user->getName() . "</td>";
               
               if ($can_write) {
                  $content .= "<td class = 'center'>";
                  $content .= "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/bin16.png'
                  onclick='popupToDelete" . $row['id'] . ".dialog(\"open\")'
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
         $content .= "<tr class='headerRow'><th colspan='6'>" . __("Info ticket MantisBT", "mantis") . "</th></tr>";
         $content .= "<td class='center'>" . __("GLPI ticket is not attached to any MantisBT ticket(s)", "mantis") . "</td>";
         $content .= "</table>";
      }
      
      echo $content;
   }

   /**
    * Function to check if link between glpi items and mantis issue exist
    *
    * @param $idItem
    * @param $id_mantis
    * @param $itemType
    * @return true if succeed else false
    */
   public function IfExistLink($idItem, $id_mantis, $itemType) {
      return $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`items_id` = '" . Toolbox::cleanInteger($idItem) . "'  AND  `" . "`.`idMantis` = '" . Toolbox::cleanInteger($id_mantis) . "'  AND  `" . "`.`itemtype` = '" . $itemType . "'");
   }

   /**
    * Function to find all links record for an item and itemType
    *
    * @param $item
    * @param $itemType
    * @return Query
    */
   public function getLinkBetweenGlpiAndMantis($item, $itemType) {
      global $DB;
      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`items_id` = '" . Toolbox::cleanInteger($item->getField('id')) . "'
                        and `glpi_plugin_mantis_mantis`.`itemtype` = '" . $itemType . "' order by `glpi_plugin_mantis_mantis`.`dateEscalade`");
   }

   /**
    * Function to find all links record for an item
    *
    * @return Query
    */
   public static function getAllLinkBetweenGlpiAndMantis() {
      global $DB;
      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.* FROM `glpi_plugin_mantis_mantis`");
   }
}
