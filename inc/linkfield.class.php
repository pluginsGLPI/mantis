<?php

include("../../../inc/includes.php");


class PluginMantisLinkfield extends CommonDBTM {


    static function canCreate() {

        if (isset($_SESSION["glpi_plugin_mantis_linkfield"])) {
            return ($_SESSION["glpi_plugin_mantis_linkfield"]['mantis'] == 'w');
        }
        return false;
    }

    static function canView() {

        if (isset($_SESSION["glpi_plugin_mantis_linkfield"])) {
            return ($_SESSION["glpi_plugin_mantis_linkfield"]['mantis'] == 'w'
                || $_SESSION["glpi_plugin_mantis_linkfield"]['mantis'] == 'r');
        }
        return false;
    }



    static function createLink() {

        echo "create insert";
        $myChamps = new self();
        // ajout des lien entre champs glpi et mantis
        $myChamps->add(array('id' => 1,'fieldMantis' => 0));
        $myChamps->add(array('id' => 2,'fieldMantis' => 0));
        $myChamps->add(array('id' => 3,'fieldMantis' => 0));
        $myChamps->add(array('id' => 4,'fieldMantis' => 0));
        $myChamps->add(array('id' => 5,'fieldMantis' => 0));
        $myChamps->add(array('id' => 6,'fieldMantis' => 0));
        $myChamps->add(array('id' => 7,'fieldMantis' => 0));
        $myChamps->add(array('id' => 8,'fieldMantis' => 0));
        $myChamps->add(array('id' => 9,'fieldMantis' => 0));
        $myChamps->add(array('id' => 10,'fieldMantis' => 0));
        $myChamps->add(array('id' => 11,'fieldMantis' => 0));
        $myChamps->add(array('id' => 12,'fieldMantis' => 0));
        $myChamps->add(array('id' => 13,'fieldMantis' => 0));
        $myChamps->add(array('id' => 14,'fieldMantis' => 0));
        $myChamps->add(array('id' => 15,'fieldMantis' => 0));
        $myChamps->add(array('id' => 16,'fieldMantis' => 0));
        $myChamps->add(array('id' => 17,'fieldMantis' => 0));
        $myChamps->add(array('id' => 18,'fieldMantis' => 0));
        $myChamps->add(array('id' => 19,'fieldMantis' => 0));

    }



    public function getAllLink(){

        global $DB;
        $field = array();

        $query = "SELECT glpi_plugin_mantis_linkfields.* from glpi_plugin_mantis_linkfields";
        $result = $DB->query($query) or die("erreur lors de la recuperation des liens entre champs ".$DB->error());

        while ($row = $result->fetch_assoc()) {
            $field[$row["id"]] = $row["fieldMantis"];
        }

        return $field;
    }


    function updateLinkField($_POST){

        //$this->getFromDB($_POST['idGlpi']);
        $resp =  $this->update($_POST);
        //var_dump($resp);
        return $resp;


    }


}