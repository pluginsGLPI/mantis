<?php

/**
 * Class PluginMantis -> class générale du plugin Mantis
 */
class PluginMantisMantis extends CommonDBTM {


    /**
     * Définition du nom de l'onglet
     **/
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        if ($item->getType() == 'Ticket') {
            return "MantisBT";
        }
       return '';
    }

    static function getTypeName($nb = 0) {
        return __("Ticket");
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
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

        if ($item->getType() == 'Ticket') {
            $monplugin = new self();
            //$ID = $item->getField('id');
            // j'affiche le formulaire
            $monplugin->showForm($item);
        }
        return true;
    }

    public function showForm($item)
    {
        //var_dump($item);
        include_once("config.class.php");
        include_once("mantisWS.class.php");
        //on recupere la config de mantis
        $config = new PluginMantisConfig();
        $config->getFromDB(1);

        //on test si le plugin (WebService) est bien configurer
        $ws = new PluginMantisMantisws();
        if($ws->testConnectionWS($config->getField('host'),$config->getField('url'),$config->getField('login'),$config->getField('pwd'))){

            global $CFG_GLPI;
            $target = array('target' => $CFG_GLPI["root_doc"] .
                "/plugins/mantis/front/mantis.form.php");

            $find = $this->getFromDBByQuery($this->getTable() . " WHERE `" . "`.`idTicket` = '" . Toolbox::cleanInteger($item->getField('id')) . "'");


            if($find){
                $this->getFormForDisplayInfo($item);
            }else{
                $this->getFormForExportTicketGlpiToMantisBT($item,$target);
            }

        }else{

            global $CFG_GLPI;
            echo"<idv class='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
            echo"<b>Merci de configurer le plugin Mantis</b></div>";

        }




    }

    /**
     * Form to climb glpi ticket to MantisBT
     * @param $item
     * @param array $option
     */
    private function getFormForExportTicketGlpiToMantisBT($item, $option = array()){

        $target = $this->getFormURL();
        if (isset($options['target'])) {
            $target = $options['target'];
        }

        //on recupere les projets et les cotégories
        include_once("mantisWS.class.php");
        $mantisWS = new PluginMantisMantisws();
        $mantisWS->initializeConnection();


        echo "<form method='post' action=".$target." >";
        echo "<table class='tab_cadre_fixe' cellpadding='5'>";
        echo "<tr class='headerRow'><th colspan='6'>Escalader ticket vers MantisBT</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Projet</th><td>";
        echo "dropdown";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Catégorie</th><td>";
        echo "dropdown";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Resumé</th>";
        echo "<td><input  id='resume' type='text' name='resume' size=35/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Description</th>";
        echo "<td><textarea  rows=10 cols=40 name='description'></textarea></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Etapes pour reproduire</th>";
        echo "<td><textarea  rows=10 cols=40 name='stepToReproduce'></textarea></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Pièces jointes</th>";
        echo "<td><INPUT type='checkbox' name='followAttachment' value=1>Faire suivre les pièces jointes du ticket Glpi dans MantisBT</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><input type='hidden' class='center' name='idTicket' id='idTicket' value=".$item->fields["id"]." class='submit'>";
        echo "<input type='hidden' class='center' name='user' id='user' value=".Session::getLoginUserID()." class='submit'>";
        echo "<input type='hidden' class='center' name='dateEscalade' id='dateEscalade' value=".date("Y-m-d")." class='submit'>";
        echo "<input id='escalade' type='submit' name='escalade' value='Escalader vers MantisBT' class='submit'></td>";


        echo "</table>";
        Html::closeForm();

    }


    /**
     * Form to display information from MantisBT
     * @param $item
     */
    private function getFormForDisplayInfo($item)
    {

        echo "<form method='post' action='#' >";
        echo "<table class='tab_cadre_fixe' cellpadding='5'>";
        echo "<tr class='headerRow'><th colspan='6'>Info du ticket MantisBT</th></tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<th>Etat</th>";
        echo "<td><input  id='etatIssue' type='text' name='etatIssue' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Lien</th>";
        echo "<td><a href='http://www.commentcamarche.net'>Lien</a></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Identifiant</th>";
        echo "<td><input  id='idIssue' type='text' name='idIssue' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Titre</th>";
        echo "<td><input  id='titleIssue' type='text' name='titleIssue' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Catégorie</th>";
        echo "<td><input  id='cateIssue' type='text' name='cateIssue' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Etat MantisBT</th>";
        echo "<td><input  id='etatMantis' type='text' name='etatMantis' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Date escalade</th>";
        echo "<td><input  id='dateEscalade' type='text' name='dateEscalade' size=35 readonly/></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>Utilisateur</th>";
        echo "<td><input  id='userEscalade' type='text' name='userEscalade' size=35 readonly/></td></tr>";


        echo "</table>";
        Html::closeForm();

    }


    /**
     * function to climb glpi ticket to mantis issue
     * @param $_myPost
     */
    public function exportGlpiIssueToMantisBT($_myPost)
    {

        //creation d'une entré dans la base pour lié le ticket Glpi a MantisBT
        $this->add($_myPost);

    }


}
