<?php

/**
 * Class PluginMantisProfile pour la gestion des profiles
 */
class PluginMantisProfile extends CommonDBTM {

    static function canCreate() {

        if (isset($_SESSION["glpi_plugin_mantis_profile"])) {
            return ($_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'w');
        }
        return false;
    }

    static function canView() {

        if (isset($_SESSION["glpi_plugin_mantis_profile"])) {
            return ($_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'w'
                || $_SESSION["glpi_plugin_mantis_profile"]['mantis'] == 'r');
        }
        return false;
    }

    static function createAdminAccess($ID) {

        $myProf = new self();
        // si le profile n'existe pas déjà dans la table profile de mon plugin
        if (!$myProf->getFromDB($ID)) {
            // ajouter un champ dans la table comprenant l'ID du profil d la personne connecté et le droit d'écriture
            $myProf->add(array('id' => $ID,'droit' => 'w'));
        }
    }

}
