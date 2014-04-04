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
            $content .= '<b>Merci de configurer le plugin Mantis</b></div>';
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
            array('title' => 'Lier ticket Glpi',
                'width' => 550,
                'height' => 125));

        Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
            '../../glpi/plugins/mantis/front/mantis.form.php?action=linkToProject&idTicket=' . $item->fields['id'],
            array('title' => 'Lier ticket Glpi',
                'width' => 600,
                'height' => 390));

        $content .= '<table id=\'table1\'  class=\'tab_cadre_fixe\' cellpadding=\'5\'>';
        $content .= '<th colspan=\'6\'>Lier le ticket Glpi à MantisBT</th>';
        $content .= '<tr class=\'tab_bg_1\'>';
        $content .= '<td align=\'center\'><input  onclick=\'popupLinkGlpiIssuetoMantisIssue.show();\'  value=\'Lier à un ticket MantisBT\' class=\'submit\'></td>';
        $content .= '<td align=\'center\'><input  onclick=\'popupLinkGlpiIssuetoMantisProject.show();\'  value=\'Lier à un projet MantisBT\' class=\'submit\'></td>';
        $content .= '</tr>';

        echo $content;

    }


    /**
     * Form to link glpi ticket to Mantis Ticket
     * @param $idTicket
     */
    public function getFormForLinkGlpiTicketToMantisTicket($idTicket)
    {

        echo "<form action='#' >";
        echo "<table class='tab_cadre' cellpadding='5'>";
        echo "<tr><th colspan='6'>Lier un ticket Glpi à un ticket MantisBT</th></tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<th width='100'>Id issue Mantis</th>";
        echo "<td width='10'><input   id='idMantis' type='text' name='idMantis'/></td>";
        echo "<td width='5'><img id='searchImg' alt='rechercher' src='/glpi/pics/aide.png' onclick='ifExistissueWithId();'  style='cursor: pointer; '/></td>";
        echo "<td width='15' id='infoFindIssueMantis'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><input  id='linktoIssue'  name='linktoIssue' value='Lier' class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";
        echo "<td width='150' id='infoLinIssueGlpiToIssueMantis'></td>";
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
    public function getFormForLinkGlpiTicketToMantisProject($idTicket)
    {


        echo "<form action='#' >";
        echo "<table id='table2' class='tab_cadre' cellpadding='5'>";
        echo "<tr class='headerRow'><th colspan='6'>Lier le ticket Glpi à un projet MantisBT</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Nom du projet</th>";
        echo "<td id='tdSearch'><input  id='nameMantisProject' type='text' name='resume' />";
        echo "<img id='searchImg' alt='rechercher' src='/glpi/pics/aide.png' onclick='findProjectByName();'  style='cursor: pointer;padding-left:5px; padding-right:5px;  '/></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Catégorie</th><td>";
        echo Dropdown::showFromArray('categorie', array(), array('rand' => ''));
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Resumé</th>";
        echo "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Description</th>";
        echo "<td><textarea  rows='5' cols='55' name='description' id='description'></textarea></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Etapes pour reproduire</th>";
        echo "<td><textarea  rows='5' cols='55' name='stepToReproduce' id='stepToReproduce'></textarea></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Pièces jointes</th>";
        echo "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' >Faire suivre les pièces jointes du ticket Glpi dans MantisBT</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=" . $idTicket . " class='submit'>";
        echo "<input type='hidden' class='center' name='user' id='user' value=" . Session::getLoginUserID() . " class='submit'>";
        echo "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=" . date("Y-m-d") . " class='submit'>";
        echo "<input  id='linktoProject' onclick='linkIssueglpiToProjectMantis();' name='linktoProject' value='Lier à un projet MantisBT' class='submit'></td>";
        echo "<td width='150' id='infoLinIssueGlpiToProjectMantis'></td>";
        echo "</table>";

        Html::closeForm();

    }


    /**
     * Form to display information from MantisBT
     * @param $item
     */
    private function getFormForDisplayInfo($item)
    {

        GLOBAL $DB;

        //on recupere l'ensemble des lien entre ticket glpi et ticket mantis
        $res = $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis` WHERE `glpi_plugin_mantis_mantis`.`idTicket` = '" . Toolbox::cleanInteger($item->getField('id')) . "'");

        if ($res->num_rows > 0) {


            $user = new User();
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();

            while ($row = $res->fetch_assoc()) {

                $user->getFromDB($row["user"]);
                $issue = $ws->getIssueById($row["idMantis"]);

                echo "<table class='tab_cadre' cellpadding='5'>";
                echo "<tr class='headerRow'><th colspan='6'>Info du ticket MantisBT n° " . $issue->id . " </th></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Etat</th>";
                echo "<td><input  id='etatIssue' type='text' name='etatIssue' size=35 value='' readonly /></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Lien</th>";
                echo "<td><a href='http://www.commentcamarche.net'>Lien</a></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Identifiant</th>";
                echo "<td><input  id='idIssue' type='text' name='idIssue' value='" . $issue->id . "' size=35 readonly/></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Titre</th>";
                echo "<td><input  id='titleIssue' type='text' name='titleIssue' value='" . $issue->summary . "' size=35 readonly/></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Catégorie</th>";
                echo "<td><input  id='cateIssue' type='text' name='cateIssue' value='" . $issue->project->name . "'size=35 readonly/></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Etat MantisBT</th>";
                echo "<td><input  id='etatMantis' type='text' name='etatMantis' value='" . $issue->status->name . "' size=35 readonly/></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Date escalade</th>";
                echo "<td><input  id='dateEscalade' type='text' name='dateEscalade' size=35 value='" . $row["dateEscalade"] . "' readonly/></td></tr>";

                echo "<tr class='tab_bg_1'>";
                echo "<th>Utilisateur</th>";
                echo "<td><input   id='userEscalade' type='text' name='userEscalade' value='" . $user->getField('realname') . " " . $user->getfield('firstname') . "' size=35 readonly/></td></tr>";


                echo "</table>";
            }

        } else {

            echo "<table class='tab_cadre' cellpadding='5'>";
            echo "<tr class='headerRow'><th colspan='6'>Info du ticket MantisBT</th></tr>";
            echo "<td>le ticket Glpi n'est lié à aucun ticket(s) mantisBT</td>";
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
        return $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`idTicket` = '" . Toolbox::cleanInteger($idTicket) . "'  AND  `" . "`.`idMantis` = '" . Toolbox::cleanInteger($idMantisIssue) . "'");
    }


}
