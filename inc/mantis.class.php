<?php
require_once("mantisWS.class.php");
require_once("mantis.class.php");
include_once("config.class.php");

/**
 * Class PluginMantis -> class générale du plugin Mantis
 */
class PluginMantisMantis extends CommonDBTM
{

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

      Toolbox::logInFile("mantis", "Début de la mise à jour des tickets Glpi\n");

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      if ($conf->getField('etatMantis')) {

         $etat_mantis = $conf->getField('etatMantis');
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();
         $res = self::getAllLinkBetweenGlpiAndMantis();




         while ($row = $res->fetch_assoc()) {

            $id_mantis = $row["idMantis"];
            $id_glpi   = $row["idTicket"];

            $ticket_glpi = new Ticket();
            $ticket_glpi->getFromDB($id_glpi);

            $ticket_mantis = $ws->getIssueById($id_mantis);


            //si le ticket n'est pas clos , et que l'etat mantis correspond a celui choisi pour
            //l'update
            if($ticket_mantis->status->name == $etat_mantis
               && $ticket_glpi->fields['status'] != 6){

               $ticket_glpi->fields['status'] = Ticket::CLOSED;
               $ticket_glpi->fields['closedate'] = date("Y-m-d");
               $ticket_glpi->fields['solvedate'] = date("Y-m-d");
               $ticket_glpi->update($ticket_glpi->fields);

               Toolbox::logInFile("mantis", "Changement d'etat pour le ticket Glpi ".$id_glpi."\n");
            }else{
               Toolbox::logInFile("mantis", "Pas de changement pour le ticket Glpi ".$id_glpi."\n");
            }

         }


         Toolbox::logInFile("mantis", "La mise à jour des tickets Glpi est terminé\n");


      } else {
         Toolbox::logInFile("mantis", "La tâche n'a pu être démarré car l'état mantis n'est pas
         renseigné \n");
      }


   }


   /**
    * Define tab name
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $id = $_SESSION['glpiactiveprofile']['id'];
      if (PluginMantisProfile::canViewMantis($id) || PluginMantisProfile::canWriteMantis($id))
         if ($item->getType() == 'Ticket') {
            return 'MantisBT';
         }
      return '';
   }

   static function getTypeName($nb = 0) {
      return __('Ticket');
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


         //if user profil can write
         if (PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id'])) {
            $this->displayBtnToLinkissueGlpi($item);
            $this->getFormForDisplayInfo($item);
         //if user can view
         } else if (PluginMantisProfile::canViewMantis($_SESSION['glpiactiveprofile']['id'])) {
            $this->getFormForDisplayInfo($item);
         }

      } else {

         $content = "";
         $content .= "<div class='center'>";
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

      $content = "";
      $content .= "<div id='popupLinkGlpiIssuetoMantisIssue' ></div>";
      $content .= "<div id='popupLinkGlpiIssuetoMantisProject' ></div>";

      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisIssue',
         $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToIssue&idTicket=' .
         $item->fields['id'], array('title'  => 'Lier ticket Glpi',
                                    'width'  => 520,
                                    'height' => 115));

      Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
         $CFG_GLPI["root_doc"] . '/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' .
         $item->fields['id'], array('title'  => 'Lier ticket Glpi',
                                    'width'  => 620,
                                    'height' => 390));

      $content .= "<table id='table1'  class='tab_cadre_fixe' >";
      $content .= "<th colspan='6'>".__("Link a Glpi ticket to MantisBT", "mantis")."</th>";

      $content .= "<tr class='tab_bg_1'>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisIssue.show();'  value='" .
         __('Link to a ticket MantisBT', 'mantis') ."' class='submit'></td>";

      $content .= "<td style='text-align: center;'>";
      $content .= "<input  onclick='popupLinkGlpiIssuetoMantisProject.show();'  value='" .
         __('Create MantisBT ticket', 'mantis') ."' class='submit'></td>";

      $content .= "</tr>";

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

      $content = "";

      $content .= "<form action='#' id=".$id_link.">";

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

      $content = "";

      $content.= "<form action='#' >";
      $content.= "<table class='tab_cadre'cellpadding='5'>";
      $content.= "<th colspan='6'>Lier un ticket Glpi à un ticket MantisBT</th>";

      $content.= "<tr class='tab_bg_1'>";
      $content.= "<th width='100'>".__('Id Ticket','mantis')."</th>";
      $content.= "<td ><input size=35 id='idMantis' type='text' name='idMantis'/></td>";
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

      $content = "";
      $content .= "<form action='#' >";
      $content .= "<table id='table2' class='tab_cadre' cellpadding='5'>";
      $content .= "<tr class='headerRow'><th colspan='6'>".
         __("Link a Glpi ticket to a MantisBT project","mantis")."</th></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>Nom du projet</th>";
      $content .= "<td id='tdSearch' height='24'>";
      $content .= "<input  id='nameMantisProject' type='text'  name='resume' />";
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

      GLOBAL $DB;
      global $CFG_GLPI;
      $can_write = PluginMantisProfile::canWriteMantis($_SESSION['glpiactiveprofile']['id']);
      $content = "";

      //on recupere l'ensemble des lien entre ticket glpi et ticket(s) mantis
      $res = $this->getLinkBetweenGlpiAndMantis($item);

      if ($res->num_rows > 0) {

         $content .= "<table class='tab_cadre_fixe'>";

         $content .= "<tr class='headerRow'>";
         $content .= "<th>" . __("Link", "mantis") . "</th>";
         $content .= "<th>" . __("Id", "mantis") . "</th>";
         $content .= "<th>" . __("Summary", "mantis") . "</th>";
         $content .= "<th>" . __("Category", "mantis") . "</th>";
         $content .= "<th>" . __("MantisBT status", "mantis") . "</th>";
         $content .= "<th>" . __("Date", "mantis") . "</th>";
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
               $content .= "<td class = 'center'><img src='".$CFG_GLPI['root_doc'].
                  "/plugins/mantis/pics/cross16.png'/></td>";
               $content .= "<td>" . $row["idMantis"] . "</td>";

               if ($can_write) {
                  $content .= "<td colspan='5'>" .
                     __('This ticket does not in the  MantisBT database', 'mantis') . "</td>";
                  $content .= "<td class = 'center'> <img src='".$CFG_GLPI['root_doc'].
                     "/plugins/mantis/pics/bin16.png'  onclick='popupToDelete" . $row['id'] .
                     ".show()';   style='cursor: pointer;' title=" . __("Delete link", "mantis") .
                     "/></td>";
                  $content .= "</tr>";
               } else {
                  $content .= "<td colspan='6'>" . __('This ticket does not in the  MantisBT database','mantis') . "</td>";
                  $content .= "</tr>";
               }

            } else {

               $content .= "<tr>";
               $content .= "<td class = 'center'>";
               $content .= "<a href='http://".$conf->fields['host']."/mantis/view.php?id=" . $issue->id . "' target='_blank' >";
               $content .= "<img src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/arrowRight16.png'/>";
               $content .= "</a></td>";
               $content .= "<td>" . $issue->id . "</td>";
               $content .= "<td>" . stripslashes($issue->summary) . "</td>";
               $content .= "<td>" . $issue->project->name . "</td>";
               $content .= "<td>" . $issue->status->name . "</td>";
               $content .= "<td>" . $row["dateEscalade"] . "</td>";
               $content .= "<td>" . $user->getField('realname') . " " . $user->getfield('firstname') . "</td>";

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
