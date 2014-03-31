<?php
class PluginMantisChampsmantisbt extends CommonDBTM {


    static function canCreate() {

        if (isset($_SESSION["glpi_plugin_mantis_champsmantisbt"])) {
            return ($_SESSION["glpi_plugin_mantis_champsmantisBT"]['mantis'] == 'w');
        }
        return false;
    }

    static function canView() {

        if (isset($_SESSION["glpi_plugin_mantis_champsmantisbt"])) {
            return ($_SESSION["glpi_plugin_mantis_champsmantisbt"]['mantis'] == 'w'
                || $_SESSION["glpi_plugin_mantis_champsmantisbt"]['mantis'] == 'r');
        }
        return false;
    }

    static function createChampsMantis() {

        $myChamps = new self();
        // ajout des champs de mantisBT
        //les champs commenté correspondent aux champs complétés par le user dans le formulaire d'escalade du icket glpi vers mantis

        //$myChamps->add(array('id' => null,'libelle' => 'project'));
        //$myChamps->add(array('id' => null,'libelle' => 'category'));
        $myChamps->add(array('id' => null,'libelle' => 'view_status'));
        $myChamps->add(array('id' => null,'libelle' => 'date_submitted'));
        $myChamps->add(array('id' => null,'libelle' => 'last_update'));
        $myChamps->add(array('id' => null,'libelle' => 'reporter'));
        $myChamps->add(array('id' => null,'libelle' => 'assigned_to'));
        $myChamps->add(array('id' => null,'libelle' => 'priority'));
        $myChamps->add(array('id' => null,'libelle' => 'severity'));
        $myChamps->add(array('id' => null,'libelle' => 'reproducibility'));
        $myChamps->add(array('id' => null,'libelle' => 'status'));
        $myChamps->add(array('id' => null,'libelle' => 'resolution'));
        $myChamps->add(array('id' => null,'libelle' => 'platform'));
        $myChamps->add(array('id' => null,'libelle' => 'OS'));
        $myChamps->add(array('id' => null,'libelle' => 'OS_version'));
        //$myChamps->add(array('id' => null,'libelle' => 'summary'));
        //$myChamps->add(array('id' => null,'libelle' => 'description'));
        //$myChamps->add(array('id' => null,'libelle' => 'step_to_reproduce'));
        $myChamps->add(array('id' => null,'libelle' => 'additional_information'));
        $myChamps->add(array('id' => null,'libelle' => 'tags'));
        $myChamps->add(array('id' => null,'libelle' => 'attached_tags'));
        //$myChamps->add(array('id' => null,'libelle' => 'attached_files'));
        $myChamps->add(array('id' => null,'libelle' => 'note'));

    }


    public function getAllFields(){

        global $DB;
        $field = array();

        $query = "SELECT glpi_plugin_mantis_champsmantisbts.* from glpi_plugin_mantis_champsmantisbts";
        $result = $DB->query($query) or die("erreur lors de la recuperation des champs Mantis ".$DB->error());

        $field[0] = 'none';
        while ($row = $result->fetch_assoc()) {
            $field[$row["id"]] = $row["libelle"];
        }

        return $field;
    }


}