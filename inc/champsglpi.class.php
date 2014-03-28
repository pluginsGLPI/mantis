<?php
class PluginMantisChampsglpi extends CommonDBTM {


    static function canCreate() {

        if (isset($_SESSION["glpi_plugin_mantis_champsglpi"])) {
            return ($_SESSION["glpi_plugin_mantis_champsglpi"]['mantis'] == 'w');
        }
        return false;
    }

    static function canView() {

        if (isset($_SESSION["glpi_plugin_mantis_champsglpi"])) {
            return ($_SESSION["glpi_plugin_mantis_champsglpi"]['mantis'] == 'w'
                || $_SESSION["glpi_plugin_mantis_champsglpi"]['mantis'] == 'r');
        }
        return false;
    }

    static function createChampsGlpi() {


        $myChamps = new self();
        // ajout des champs de glpi
        $myChamps->add(array('id' => null,'libelle' => 'opening_date'));
        $myChamps->add(array('id' => null,'libelle' => 'by'));
        $myChamps->add(array('id' => null,'libelle' => 'type'));
        $myChamps->add(array('id' => null,'libelle' => 'status'));
        $myChamps->add(array('id' => null,'libelle' => 'urgency'));
        $myChamps->add(array('id' => null,'libelle' => 'impact'));
        $myChamps->add(array('id' => null,'libelle' => 'priority'));
        $myChamps->add(array('id' => null,'libelle' => 'due_date'));
        $myChamps->add(array('id' => null,'libelle' => 'last_update'));
        $myChamps->add(array('id' => null,'libelle' => 'categorie'));
        $myChamps->add(array('id' => null,'libelle' => 'request_source'));
        $myChamps->add(array('id' => null,'libelle' => 'approval'));
        $myChamps->add(array('id' => null,'libelle' => 'associated_element'));
        $myChamps->add(array('id' => null,'libelle' => 'location'));
        $myChamps->add(array('id' => null,'libelle' => 'requester'));
        $myChamps->add(array('id' => null,'libelle' => 'watcher'));
        $myChamps->add(array('id' => null,'libelle' => 'assigned_to'));
        $myChamps->add(array('id' => null,'libelle' => 'title'));
        $myChamps->add(array('id' => null,'libelle' => 'description'));

    }

    public function getAllFields(){

        global $DB;
        $field = array();

        $query = "SELECT glpi_plugin_mantis_champsglpis.* from glpi_plugin_mantis_champsglpis";
        $result = $DB->query($query) or die("erreur lors de la recuperation des champs GLPI".$DB->error());

        while ($row = $result->fetch_assoc()) {
            $field[$row["id"]] = $row["libelle"];
        }

        return $field;
    }


}