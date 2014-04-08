<?php
require_once("mantisWS.class.php");
require_once("mantis.class.php");
include_once("config.class.php");

/**
 * Class PluginMantis -> class générale du plugin Mantis
 */
class PluginMantisMantis extends CommonDBTM
{


   /**
    * Définition du nom de l'onglet
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
    * Définition du contenu de l'onglet
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

      //on recupere la config de mantis
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      //on test si le plugin (WebService) est bien configurer
      $ws = new PluginMantisMantisws();
      if ($ws->testConnectionWS($conf->getField('host'), $conf->getField('url'),
         $conf->getField('login'), $conf->getField('pwd'))
      ) {

         $id = $_SESSION['glpiactiveprofile']['id'];
         if (PluginMantisProfile::canWriteMantis($id)) {
            $this->displayBtnToLinkissueGlpi($item);
            $this->getFormForDisplayInfo($item);
         } else if (PluginMantisProfile::canViewMantis($id)) {
            $this->getFormForDisplayInfo($item);
         }

      } else {

         global $CFG_GLPI;
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


   public function getFormToDelLinkOrissue($id, $idTicket, $idMantis){
      global $CFG_GLPI;
      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();
      $issue = $ws->getIssueById($idMantis);

      $content = "";

      $content .= "<form action='#' id=".$id.">";

      $content .= "<table id=".$id." class='tab_cadre' cellpadding='5' >";
      $content .= "<th colspan='2'>".__("What do you do ?","mantis")."</th>";


      $content .= "<tr class='tab_bg_1' >";
      $content .= "<td><INPUT type='checkbox'  id='deleteLink".$id."' >";
      $content .= __("Delete link of the MantisBT ticket","mantis")."</td>";
      $content .= "<td>".__("(Does not delete the MantisBT ticket)","mantis")."</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      if (!$issue) {
         $content .= "<td><INPUT type='checkbox' disabled id='deleteIssue" . $id . "' >";
         $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
      } else {
         $content .= "<td><INPUT type='checkbox' id='deleteIssue" . $id . "' >";
         $content .= __("Delete the  MantisBT ticket", "mantis") . "</td>";
      }
      $content .= "<td>".__("(Also removes the link in GLPI)","mantis")."</td>";
      $content .= "</tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<td><input  id=" . $id . "  name='delo' value='" . __("Delete", "mantis") .
         "' class='submit' onclick='delLinkAndOrIssue(" .$id . "," . $idMantis . "," . $idTicket . ");'></td>";
      $content .= "<td><div id='infoDel" . $id . "' ></div>";
      $content .= "<img id='waitDelete" . $id . "' src='".$CFG_GLPI["root_doc"]."/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      $content .= "</tr>";

      $content .= "<input type='hidden' name='idMantis".$id."' id='idMantis' value=" . $id . "       >";
      $content .= "<input type='hidden' name='id".$id."'       id='id'       value=" . $idMantis . " >";
      $content .= "<input type='hidden' name='idTicket".$id."' id='idticket' value=" . $idTicket . " >";

      $content .= "</table>";

      $content .= Html::closeForm(false);

      echo $content;

   }




   /**
    * Form to link glpi ticket to Mantis Ticket
    * @param $idTicket
    */
   public function getFormForLinkGlpiTicketToMantisTicket($idTicket) {

      global $CFG_GLPI;

      $content = "";

      $content.= "<form action='#' >";
      $content.= "<table class='tab_cadre' cellpadding='5'>";
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


      $content.= "<input type='hidden' name='idTicket' id='idTicket' value=" .  $idTicket . " >";
      $content.= "<input type='hidden' name='user' id='user' value=" . Session::getLoginUserID(). " >";
      $content.= "<input type='hidden' name='dateEscalade' id='dateEscalade' value=" .date("Y-m-d") . " >";

      $content.= "</table>";

      $content.= Html::closeForm(false);

      echo $content;

   }


   /**
    * Form to link glpi ticket to mantis project
    * @param $idTicket
    */
   public function getFormForLinkGlpiTicketToMantisProject($idTicket) {

      global $CFG_GLPI;

      $content = "";
      $content .= "<form action='#' >";
      $content .= "<table id='table2' class='tab_cadre' cellpadding='5'>";
      $content .= "<tr class='headerRow'><th colspan='6'>".
         __("Link a Glpi ticket to a MantisBT project","mantis")."</th></tr>";

      $content .= "<tr class='tab_bg_1'>";
      $content .= "<th>Nom du projet</th>";
      $content .= "<td id='tdSearch' height='24'><input  id='nameMantisProject' type='text' name='resume' />";
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
         $idTicket . " class='submit'>";
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

      //on recupere l'ensemble des lien entre ticket glpi et ticket mantis
      $res = $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`idTicket` = '" . Toolbox::cleanInteger($item->getField('id')) . "'
                        order by `glpi_plugin_mantis_mantis`.`dateEscalade`");


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
            /** @var Object $issue */
            $issue = $ws->getIssueById($row["idMantis"]);
            $conf->getFromDB(1);



            $content .= '<div id=\'popupToDelete' . $row['id'] . '\'></div>';

            Ajax::createModalWindow('popupToDelete' . $row['id'],
               $CFG_GLPI['root_doc'].'/plugins/mantis/front/mantis.form.php?action=deleteIssue&id=' .
               $row['id'] . '&idTicket=' . $row['idTicket'] . '&idMantis=' . $row['idMantis'],
               array('title'  => __("Delete", "mantis"),
                     'width'  => 520,
                     'height' => 150));

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
    * Function to check if link bewteen glpi issue and mantis issue exist
    * @param $idTicket
    * @param $idMantisIssue
    * @return true if exist else false
    */
   public function IfExistLink($idTicket, $idMantisIssue) {
      return $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`idTicket` = '" .
         Toolbox::cleanInteger($idTicket) . "'  AND  `" . "`.`idMantis` = '" .
         Toolbox::cleanInteger($idMantisIssue) . "'");
   }


}
