<?php

/*
   ------------------------------------------------------------------------
   GLPI Plugin MantisBT
   Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.

   https://forge.indepnet.net/projects/mantis
   ------------------------------------------------------------------------

   LICENSE

   This file is part of GLPI Plugin MantisBT project.

   GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   GLPI Plugin MantisBT is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   GLPI Plugin MantisBT
   @author    Stanislas Kita (teclib')
   @co-author François Legastelois (teclib')
   @co-author Le Conseil d'Etat
   @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
   @license   GPLv3 or (at your option) any later version
              http://www.gnu.org/licenses/gpl.html
   @link      https://forge.indepnet.net/projects/mantis
   @since     2014

   ------------------------------------------------------------------------
 */
   
/**
 * function to install the plugin
 * @return boolean
 */
function plugin_mantis_install()
{
   require_once('inc/mantis.class.php');
   PluginMantisMantis::install();

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



    //création de la table pour la configuration du plugin
    if (!TableExists("glpi_plugin_mantis_configs")) {
        $query = "CREATE TABLE glpi_plugin_mantis_configs (
                  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  host char(16) NOT NULL default '',
                  url char(255) NOT NULL default '',
                  login char(32) NOT NULL default '',
                  pwd char(32) NOT NULL default '',
                  champsUrlGlpi char(100) NOT NULL default '',
                  champsGlpi char(100) NOT NULL default '',
                  etatMantis char(100) NOT NULL default '')";
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

   require_once('inc/mantis.class.php');
   PluginMantisMantis::uninstall();
    return true;
}

