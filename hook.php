<?php

/**
 * function to install the plugin
 * @return boolean
 */
function plugin_mantis_install(){

    global $DB;

    if (!TableExists("glpi_plugin_mantis_profiles")) {
        $query = "CREATE TABLE glpi_plugin_mantis_profiles (
               id int(11) NOT NULL PRIMARY KEY ,
               droit char(1) NOT NULL default '')";

        $DB->query($query) or die($DB->error());



        //creation du premier accès nécessaire lors de l'installation du plugin
        include_once("inc/profile.class.php");
        PluginMantisProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
    }

    if (!TableExists("glpi_plugin_mantis_configs")) {
        $query = "CREATE TABLE glpi_plugin_mantis_configs (
                  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  urlwsdl char(255) NOT NULL default '',
                  login char(32) NOT NULL default '',
                  pwd char(32) NOT NULL default '')";

        $DB->query($query) or die($DB->error());
        return true;
    }




}


/**
 * function to uninstall the plugin
 * @return boolean
 */
function plugin_mantis_uninstall(){

    global $DB;

    $tables = array("glpi_plugin_mantis_configs","glpi_plugin_mantis_profiles");

    Foreach($tables as $table){
        $DB->query("DROP TABLE IF EXISTS `$table`;");
    }


    return true;
}
