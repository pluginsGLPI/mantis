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
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Ticket') {
            return 'MantisBT';
        }
        return '';
    }

    static function getTypeName($nb = 0)
    {
        return __('Ticket');
    }

    static function canCreate()
    {
        return Session::haveRight('ticket', 'w');
    }

    static function canView()
    {
        return Session::haveRight('ticket', 'r');
    }


    /**
     * Définition du contenu de l'onglet
     **/
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Ticket') {
            $monplugin = new self();
            //$ID = $item->getField('id');
            // j'affiche le formulaire
            $monplugin->showForm($item);
        }
        return true;
    }


    /**
     * Function to show the form of plugin
     * @param $item
     */
    public function showForm($item)
    {

        //on recupere la config de mantis
        $conf = new PluginMantisConfig();
        $conf->getFromDB(1);

        //on test si le plugin (WebService) est bien configurer
        $ws = new PluginMantisMantisws();
        if ($ws->testConnectionWS($conf->getField('host'), $conf->getField('url'), $conf->getField('login'), $conf->getField('pwd'))) {

            $this->displayBtnToLinkissueGlpi($item);
            $this->getFormForDisplayInfo($item);

        } else {

            global $CFG_GLPI;
            $content = '';
            $content .=  '<idv class=\'center\'><br><br><img src=\'' . $CFG_GLPI["root_doc"] .'/pics/warning.png\' alt=\'warning\'><br><br>';
            $content .= '<b>'.__("Thank you configure the mantis plugin","mantis").'</b></div>';
            echo $content;
        }

    }


    /**
     * function to show action give by plugin
     * @param $item
     */
    public function displayBtnToLinkissueGlpi($item)
    {

       $content = '';
       $content .= '<div id=\'popupLinkGlpiIssuetoMantisIssue\' ></div>';
       $content .= '<div id=\'popupLinkGlpiIssuetoMantisProject\' ></div>';

       Ajax::createModalWindow('popupLinkGlpiIssuetoMantisIssue',
          '../../glpi/plugins/mantis/front/mantis.form.php?action=linkToIssue&idTicket=' . $item->fields['id'],
          array('title'  => 'Lier ticket Glpi',
                'width'  => 520,
                'height' => 115));

       Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
          '../../glpi/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' . $item->fields['id'],
          array('title'  => 'Lier ticket Glpi',
                'width'  => 620,
                'height' => 390));

       $content .= '<table id=\'table1\'  class=\'tab_cadre_fixe\' cellpadding=\'5\'>';
       $content .= '<th colspan=\'6\'>'.__("Link a Glpi ticket to MantisBT","mantis").'</th>';
       $content .= '<tr class=\'tab_bg_1\'>';
       $content .= '<td align=\'center\'><input  onclick=\'popupLinkGlpiIssuetoMantisIssue.show();\'    value=\''.__('Link to a ticket MantisBT','mantis').'\' class=\'submit\'></td>';
       $content .= '<td align=\'center\'><input  onclick=\'popupLinkGlpiIssuetoMantisProject.show();\'  value=\''.__('Create MantisBT ticket','mantis').'\' class=\'submit\'></td>';
       $content .= '</tr>';

       echo $content;

    }


   public function getFormToDelLinkOrissue($id, $idTicket, $idMantis){

      echo "<form action='#' id=".$id.">";

      echo "<table id=".$id." class='tab_cadre' cellpadding='5' >";
      echo "<tr class='headerRow' colspan='2'><th colspan='2'>".__("What do you do ?","mantis")."</th></tr>";

      echo "<tr class='tab_bg_1' >";
      echo "<td><INPUT type='checkbox'  id='deleteLink".$id."' >".__("Delete link of the MantisBT ticket","mantis")."</td>";
      echo "<td>".__("(Does not delete the MantisBT ticket)","mantis")."</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><INPUT type='checkbox'  id='deleteIssue".$id."' >".__("Delete the  MantisBT ticket","mantis")."</td>";
      echo "<td>".__("(Also removes the link in GLPI)","mantis")."</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><input  id=".$id."  name='delo' value='".__("Delete","mantis")."' class='submit' onclick='delLinkAndOrIssue(".$id.",".$idMantis.",".$idTicket.");'></td>";
      echo "<td><div id='infoDel".$id."' ></div>";
      echo "<img id='waitDelete".$id."' src='../../glpi/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";
      echo "</tr>";

      echo "<input type='hidden' class='center' name='idMantis".$id."' id='idMantis' value=" . $id . "       class='submit'>";
      echo "<input type='hidden' class='center' name='id".$id."'       id='id'       value=" . $idMantis . " class='submit'>";
      echo "<input type='hidden' class='center' name='idTicket".$id."' id='idticket' value=" . $idTicket . " class='submit'>";

      echo "</table>";

      Html::closeForm();

   }




   /**
    * Form to link glpi ticket to Mantis Ticket
    * @param $idTicket
    */
   public function getFormForLinkGlpiTicketToMantisTicket($idTicket) {

      echo '<form action=\'#\' >';
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr><th colspan='6'>Lier un ticket Glpi à un ticket MantisBT</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='100'>".__('Id MantisBT ticket','mantis')."</th>";
      echo "<td ><input size=35 id='idMantis' type='text' name='idMantis'/></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><input  id='linktoIssue'  name='linktoIssue' value='Lier' class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";

      echo "<td width='150' height='20'>";
      echo "<div id='infoLinIssueGlpiToIssueMantis' ></div>";
      echo "<img id='waitForLinkIssueGlpiToIssueMantis' src='../../glpi/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";


      echo "</tr>";

      echo "<input type='hidden' name='idTicket'     id='idTicket'     value='" . $idTicket . "' >";
      echo "<input type='hidden' name='user'         id='user'         value=" . Session::getLoginUserID() . " >";
      echo "<input type='hidden' name='dateEscalade' id='dateEscalade' value=" . date("Y-m-d") . " >";

      echo "</table>";

      Html::closeForm();

   }


   /**
    * Form to link glpi ticket to mantis project
    * @param $idTicket
    */
   public function getFormForLinkGlpiTicketToMantisProject($idTicket) {

      echo "<form action='#' >";
      echo "<table id='table2' class='tab_cadre' cellpadding='5'>";
      echo "<tr class='headerRow'><th colspan='6'>".__("Link a Glpi ticket to a MantisBT project","mantis")."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>Nom du projet</th>";
      echo "<td id='tdSearch' height='24'><input  id='nameMantisProject' type='text' name='resume' />";
      echo "<img id='searchImg' alt='rechercher' src='/glpi/pics/aide.png' onclick='findProjectByName();'  style='cursor: pointer;padding-left:5px; padding-right:5px;  '/></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__("Category","mantis")."</th><td>";
      echo Dropdown::showFromArray('categorie', array(), array('rand' => ''));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__("Summary","mantis")."</th>";
      echo "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__("Description","mantis")."</th>";
      echo "<td><textarea  rows='5' cols='55' name='description' id='description'></textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__("Steps to reproduce","mantis")."</th>";
      echo "<td><textarea  rows='5' cols='55' name='stepToReproduce' id='stepToReproduce'></textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>".__("Attachments","mantis")."</th>";
      echo "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' >".__("To forward attachments","mantis")."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=" . $idTicket . " class='submit'>";
      echo "<input type='hidden' class='center' name='user' id='user' value=" . Session::getLoginUserID() . " class='submit'>";
      echo "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=" . date("Y-m-d") . " class='submit'>";
      echo "<input  id='linktoProject' onclick='linkIssueglpiToProjectMantis();'name='linktoProject' value='".__("Link","mantis")."' class='submit'></td>";

      echo "<td width='150' >";
      echo "<div id='infoLinkIssueGlpiToProjectMantis' ></div>";
      echo "<img id='waitForLinkIssueGlpiToProjectMantis' src='../../glpi/plugins/mantis/pics/please_wait.gif' style='display:none;'/></td>";

      echo "</table>";

      Html::closeForm();

   }


   /**
    * Form to display information from MantisBT
    * @param $item
    */
   private function getFormForDisplayInfo($item) {

      GLOBAL $DB;

      //on recupere l'ensemble des lien entre ticket glpi et ticket mantis
      $res = $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`
                        .`idTicket` = '" . Toolbox::cleanInteger($item->getField('id')) . "'
                        order by `glpi_plugin_mantis_mantis`.`dateEscalade`");



      if ($res->num_rows > 0) {




         echo "<table class='tab_cadre_fixe'>";

         echo "<tr class='headerRow' colspan='6'>";
         echo "<th>".__("Link","mantis")."</th>";
         echo "<th>".__("Id","mantis")."</th>";
         echo "<th>".__("Summary","mantis")."</th>";
         echo "<th>".__("Category","mantis")."</th>";
         echo "<th>".__("MantisBT status","mantis")."</th>";
         echo "<th>".__("Date","mantis")."</th>";
         echo "<th>".__("User","mantis")."</th>";
         echo "<th></th>";
         echo "</tr>";

         $user = new User();
         $conf = new PluginMantisConfig();
         $ws   = new PluginMantisMantisws();
         $ws->initializeConnection();

         while ($row = $res->fetch_assoc()) {

            $user->getFromDB($row["user"]);
            $issue = $ws->getIssueById($row["idMantis"]);
            $conf->getFromDB(1);



            echo '<div id=\'popupToDelete'.$row['id'].'\'></div>';

            Ajax::createModalWindow('popupToDelete'.$row['id'],
               '../../glpi/plugins/mantis/front/mantis.form.php?action=deleteIssue&id='.$row['id'].'&idTicket='.$row['idTicket'].'&idMantis='.$row['idMantis'],
               array('title'  => __("Delete","mantis"),
                     'width'  => 520,
                     'height' => 150));



            if(!$issue){
               echo "<tr>";
               echo "<td class = 'center'><img src='../../glpi/plugins/mantis/pics/cross16.png'/></td>";
               echo "<td>" . $row["idMantis"] . "</td>";
               echo "<td colspan='5'>".__('This ticket does not in the  MantisBT database','mantis')."</td>";
               echo "<td class = 'center'> <img src='../../glpi/plugins/mantis/pics/bin16.png'  onclick='popupToDelete".$row['id'].".show()';   style='cursor: pointer;' title=".__("Delete link","mantis")."/></td>";
               //echo "<td class = 'center'><img src='../../glpi/plugins/mantis/pics/bin16.png' onclick='deleteLinkGlpiMantis(".$row["id"].",".$row["idTicket"].",".$row["idMantis"].",false);'style='cursor: pointer;'" ."title=".__("Delete link","mantis")."/></td>";
               echo "</tr>";
            }else{
               echo "<tr>";
               echo "<td class = 'center'><a href='http://" . $conf->fields['host'] . "/mantis/view"
                  . ".php?id=" . $issue->id . "' target='_blank' >";
               echo "<img src='../../glpi/plugins/mantis/pics/arrowRight16.png'/></a></td>";
               echo "<td>" . $issue->id . "</td>";
               echo "<td>" . stripslashes($issue->summary) . "</td>";
               echo "<td>" . $issue->project->name . "</td>";
               echo "<td>" . $issue->status->name . "</td>";
               echo "<td>" . $row["dateEscalade"] . "</td>";
               echo "<td>" . $user->getField('realname') . " " . $user->getfield('firstname') . "</td>";
               echo "<td class = 'center'> <img src='../../glpi/plugins/mantis/pics/bin16.png'  onclick='popupToDelete".$row['id'].".show()';   style='cursor: pointer;' title=".__("Delete link","mantis")."/></td>";
               //echo "<td class = 'center'><img src='../../glpi/plugins/mantis/pics/bin16.png' onclick='deleteLinkGlpiMantis(".$row["id"].",".$row["idTicket"].",".$row["idMantis"].",true);'style='cursor: pointer;'"."title=".__("Delete link","mantis")."/></td>";

               echo "</tr>";
            }




         }

         echo "</table>";

      } else {

         //
         echo "<table class='tab_cadre' cellpadding='5'>";
         echo "<tr class='headerRow'><th colspan='6'>".__("Info ticket MantisBT","mantis")."</th></tr>";
         echo "<td>".__("GLPI ticket is not attached to any MantisBT ticket(s)","mantis")."</td>";
         echo "</table>";

      }

   }


    /**
     * Function to check if link bewteen glpi issue and mantis issue exist
     * @param $idTicket
     * @param $idMantisIssue
     * @return true if exist else false
     */
    public function IfExistLink($idTicket, $idMantisIssue)
    {
        return $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`idTicket` = '" .
           Toolbox::cleanInteger($idTicket) . "'  AND  `" . "`.`idMantis` = '" .
           Toolbox::cleanInteger($idMantisIssue) . "'");
    }


}
