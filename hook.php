<?php

/**
 * function to install the plugin
 * @return boolean
 */
function plugin_mantis_install()
{

    global $DB;


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












   // Création de la table uniquement lors de la première installation
   if (!TableExists("glpi_plugin_mantis_profiles")) {

      // requete de création de la table
      $query = "CREATE TABLE `glpi_plugin_mantis_profiles` (
               `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
               `right` char(1) collate utf8_unicode_ci default NULL,
               PRIMARY KEY  (`id`)
             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->queryOrDie($query, $DB->error());


      //creation du premier accès nécessaire lors de l'installation du plugin
      include_once("inc/profile.class.php");
      PluginMantisProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);

}
   /*
    //création de la table pour la gestion des profiles du plugin
    if (!TableExists("glpi_plugin_mantis_profiles")) {
        $query = "CREATE TABLE glpi_plugin_mantis_profiles (
               id int(11) NOT NULL PRIMARY KEY ,
               droit char(1) NOT NULL default '')";
        $DB->query($query) or die($DB->error());

        //creation du premier accès nécessaire lors de l'installation du plugin
        include_once("inc/profile.class.php");
        PluginMantisProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
    }

      */









    //création de la table pour la configuration du plugin
    if (!TableExists("glpi_plugin_mantis_configs")) {
        $query = "CREATE TABLE glpi_plugin_mantis_configs (
                  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  host char(16) NOT NULL default '',
                  url char(255) NOT NULL default '',
                  login char(32) NOT NULL default '',
                  pwd char(32) NOT NULL default '',
                  champsUrlGlpi char(100) NOT NULL default '',
                  champsGlpi char(100) NOT NULL default '')";
        $DB->query($query) or die($DB->error());

        //insertion du occcurence dans la table (occurrence par default)
        $query = "INSERT INTO glpi_plugin_mantis_configs
                       (id, host,url,login,pwd)
                VALUES (NULL, '','','','')";
        $DB->query($query) or die("erreur lors de l'insertion des valeurs par défaut dans la table de configuration " . $DB->error());

        return true;
    }

}


/**
 * function to uninstall the plugin
 * @return boolean
 */
function plugin_mantis_uninstall()
{

    global $DB;

    $tables = array("glpi_plugin_mantis_configs",
        "glpi_plugin_mantis_profiles,glpi_plugin_mantis_mantis",
        "glpi_plugin_mantis_profiles");

    Foreach ($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS " . $table . ";");
    }


    return true;
}

