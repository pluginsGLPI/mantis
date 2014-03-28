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
        $myChamps->add(array('id' => null,'libelle' => 'opening_date'       , 'indexName' => 'date'));
        $myChamps->add(array('id' => null,'libelle' => 'by'                 , 'indexName' => 'entities_id'));
        $myChamps->add(array('id' => null,'libelle' => 'type'               , 'indexName' => 'type'));
        $myChamps->add(array('id' => null,'libelle' => 'status'             , 'indexName' => 'status'));
        $myChamps->add(array('id' => null,'libelle' => 'urgency'            , 'indexName' => 'urgency'));
        $myChamps->add(array('id' => null,'libelle' => 'impact'             , 'indexName' => 'impact'));
        $myChamps->add(array('id' => null,'libelle' => 'priority'           , 'indexName' => 'priority'));
        $myChamps->add(array('id' => null,'libelle' => 'due_date'           , 'indexName' => 'due_date'));
        $myChamps->add(array('id' => null,'libelle' => 'last_update'        , 'indexName' => 'date_mod'));
        $myChamps->add(array('id' => null,'libelle' => 'categorie'          , 'indexName' => 'itilcategories_id'));
        $myChamps->add(array('id' => null,'libelle' => 'request_source'     , 'indexName' => 'requesttypes_id'));
        $myChamps->add(array('id' => null,'libelle' => 'approval'           , 'indexName' => 'global_validation'));
        $myChamps->add(array('id' => null,'libelle' => 'associated_element' , 'indexName' => ''));
        $myChamps->add(array('id' => null,'libelle' => 'location'           , 'indexName' => 'location_id'));
        $myChamps->add(array('id' => null,'libelle' => 'requester'          , 'indexName' => ''));
        $myChamps->add(array('id' => null,'libelle' => 'watcher'            , 'indexName' => ''));
        $myChamps->add(array('id' => null,'libelle' => 'assigned_to'        , 'indexName' => ''));
        $myChamps->add(array('id' => null,'libelle' => 'title'              , 'indexName' => 'name'));
        $myChamps->add(array('id' => null,'libelle' => 'description'        , 'indexName' => 'content'));
        $myChamps->add(array('id' => null,'libelle' => 'user_lastupdater'   , 'indexName' => 'user_id_lastupdater'));

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