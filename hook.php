<?php

/**
 * function to install the plugin
 * @return boolean
 */
function plugin_mantis_install(){

    global $DB;


    // création de la table des champs glpi
    if (!TableExists("glpi_plugin_mantis_linkfields")) {
        $query = "CREATE TABLE glpi_plugin_mantis_linkfields (
               id int(11) PRIMARY KEY NOT NULL,
                fieldMantis int(11) NOT NULL)";
        $DB->query($query) or die($DB->error());
    }
    include_once("inc/linkfield.class.php");
    PluginMantisLinkfield::createLink();



    // création de la table des champs glpi
    if (!TableExists("glpi_plugin_mantis_champsglpis")) {
        $query = "CREATE TABLE glpi_plugin_mantis_champsglpis (
               id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              libelle char(30) NOT NULL default '')";
        $DB->query($query) or die($DB->error());
    }
    include_once("inc/champsglpi.class.php");
    PluginMantisChampsglpi::createChampsGlpi();


    // création de la table des champs mantis
    if (!TableExists("glpi_plugin_mantis_champsmantisbts")) {
        $query = "CREATE TABLE glpi_plugin_mantis_champsmantisbts (
               id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              libelle char(30) NOT NULL default '')";
        $DB->query($query) or die($DB->error());
    }
    include_once("inc/champsmantisbt.class.php");
    PluginMantisChampsmantisbt::createChampsMantis();




    // création de la table du plugin
    if (!TableExists("glpi_plugin_mantis_mantis")) {
        $query = "CREATE TABLE glpi_plugin_mantis_mantis (
               id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
               idTicket int(11) NOT NULL,
               idMantis int(11) NOT NULL,
               dateEscalade date NOT NULL,
               user int(11) NOT NULL)";
        $DB->query($query) or die($DB->error());
    }



    //création de la table pour la gestion des profiles du plugin
    if (!TableExists("glpi_plugin_mantis_profiles")) {
        $query = "CREATE TABLE glpi_plugin_mantis_profiles (
               id int(11) NOT NULL PRIMARY KEY ,
               droit char(1) NOT NULL default '')";
        $DB->query($query) or die($DB->error());

        //creation du premier accès nécessaire lors de l'installation du plugin
        include_once("inc/profile.class.php");
        PluginMantisProfile::createAdminAccess(Session::getLoginUserID());
    }

    //création de la table pour la configuration du plugin
    if (!TableExists("glpi_plugin_mantis_configs")) {
        $query = "CREATE TABLE glpi_plugin_mantis_configs (
                  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  host char(16) NOT NULL default '',
                  url char(255) NOT NULL default '',
                  login char(32) NOT NULL default '',
                  pwd char(32) NOT NULL default '',
                  champsUrlGlpi char(100) NOT NULL default '')";
        $DB->query($query) or die($DB->error());

        //insertion du occcurence dans la table (occurrence par default)
        $query = "INSERT INTO glpi_plugin_mantis_configs
                       (id, host,url,login,pwd)
                VALUES (NULL, '','','','')";
        $DB->query($query) or die("erreur lors de l'insertion des valeurs par défaut dans la table de configuration ".$DB->error());

        return true;
    }




}


/**
 * function to uninstall the plugin
 * @return boolean
 */
function plugin_mantis_uninstall(){

    global $DB;

    $tables = array("glpi_plugin_mantis_configs",
        "glpi_plugin_mantis_profiles,glpi_plugin_mantis_mantis",
        "glpi_plugin_mantis_champsglpis",
        "glpi_plugin_mantis_champsmantisbts",
        "glpi_plugin_mantis_linkfields");

    Foreach($tables as $table){
        $DB->query("DROP TABLE IF EXISTS ".$table.";");
    }


    return true;
}

