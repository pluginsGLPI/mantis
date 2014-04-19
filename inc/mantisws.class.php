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

class PluginMantisMantisws{

   private $_host;
   private $_url;
   private $_login;
   private $_password;
   private $_client;

   function __construct() {}


   /**
    * function to initialize the connection to the Web service
    * with the configuration settings stored in BDD
    */
   function initializeConnection() {

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      if(!empty($conf->fields["host"]) && !empty($conf->fields["url"])) {
        $this->_host     = $conf->fields["host"];
        $this->_url      = $conf->fields["url"];
        $this->_login    = $conf->fields["login"];
        $this->_password = $conf->fields["pwd"];

        $this->_client = new SoapClient("http://" . $this->_host . "/" . $this->_url);
      } else {
        return false;
      }
   }

   /**
    * function to test the connectivity of the web service
    * @param $host
    * @param $url
    * @param $login
    * @param $password
    * @return bool
    * @throws Exception
    */
  function testConnectionWS($host, $url, $login, $password) {

      if(empty($host) OR empty($url)) {
         return false;
      }

      try {
         $client = new SoapClient("http://" . $host . "/" . $url);
         $client->mc_login($login, $password);
         return true;
      } catch (SoapFault $sp) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error to connect to the web service MantisBT => \'%1$s\'', 'mantis'),
               $sp->getMessage()) . "\n");

         if($sp->getMessage() ==  'Access denied'){
            return false;
         }else{
           throw new Exception($sp->getMessage());
         }

      }
   }

   /**
    * Function to find category by name of project
    * @param $name name of project
    * @return array  return categorie if find else false
    */
   public function getCategoryFromProjectName($name) {
      $id = $this->getProjectIdWithName($name);
      try {
         $response = $this->_client->mc_project_get_categories($this->_login, $this->_password, $id);
         return ($response);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error retrieving category from the project id \'%1$s\' => \'%2$s\'', 'mantis'),
               $id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * function to check if an issue exist
    * @param $_issue_id
    * @return bool
    */
   public function existIssueWithId($_issue_id) {
      try {
         $response = $this->_client->mc_issue_exists($this->_login, $this->_password, $_issue_id);
         return ($response);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(sprintf(
               __('Error when checking existence of the MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'),
               $_issue_id, $e->getMessage())) . "\n");

         return false;
      }
   }

   /**
    * Function to delete an issue with id
    * @param integer $_issue_id
    * @return boolean
    */
   public function deleteIssue($_issue_id) {
      try {
         return $this->_client->mc_issue_delete($this->_login, $this->_password, $_issue_id);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error while deleting the ticket \'%1$s\' => \'%2$s\'', 'mantis'),
               $_issue_id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Method to call the operation originally named mc_issue_note_add
    * @param integer $_issue_id
    * @param PluginMantisStructissuenotedata $_note
    * @return integer
    */
   public function addNoteToIssue($_issue_id, PluginMantisStructissuenotedata $_note) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_note_add($this->_login, $this->_password, $_issue_id, $_note);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('error while creating note => \'%1$s\'', 'mantis'),
               $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to add an attachment to an issue
    * @param integer $_issue_id
    * @param string $_name
    * @param string $_file_type
    * @param base64Binary $_content
    * @return integer
    */
   public function addAttachmentToIssue($_issue_id, $_name, $_file_type, $_content) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_attachment_add($this->_login, $this->_password,
            $_issue_id, $_name, $_file_type, $_content);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('error while creating attachment => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to add issue
    * @param $issue
    * @return Integer
    */
   function addIssue($issue) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_add($this->_login, $this->_password, $issue);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(__('Error creating MantisBT ticket \'%1$s\'', 'mantis'),
               $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to find issue by id
    * @param $idIssue
    * @return bool
    */
   function getIssueById($idIssue) {
      global $CFG_GLPI;
      try {
         $response = $this->_client->mc_issue_get($this->_login, $this->_password, $idIssue);
         return $response;
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error searching MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'),
               $idIssue, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * function to find id of project with name
    * @param $name
    * @return mixed
    */
   public function getProjectIdWithName($name) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error retrieving the id of the project by it\'s name  \'%1$s\' => \'%2$s\'', 'mantis'),
               $name, $e->getMessage()) . "\n");
         return "ERROR -> " . $e->getMessage();
      }
   }

   /**
    * function to check if project exist (with name)
    * @param $name
    * @return bool
    */
   public function existProjectWithName($name) {
      global $CFG_GLPI;
      try {
         $response = $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
         if ($response == 0) return false;
         else return true;
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error when checking the  existence of the project by his name \'%1$s\' => \'%2$s\'', 'mantis'),
               $name, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Delete the note with the specified id.
    * @param integer $_issue_note_id
    * @return boolean
    */
   public function deleteNote($_issue_note_id) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_note_delete($this->_login, $this->_password, $_issue_note_id);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error when deleting note \'%1$s\' => \'%2$s\'', 'mantis'),
               $_issue_note_id, $e->getMessage()) . "\n");
         return false;
      }
   }


   /**
    * Delete the issue attachment with the specified id.
    * @param integer $_issue_attachment_id
    * @return boolean
    */
   public function deleteAttachment($_issue_attachment_id) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_attachment_delete($this->_login, $this->_password, $_issue_attachment_id);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error when deleting attachment \'%1$s\' => \'%2$s\'', 'mantis'),
               $_issue_attachment_id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Get the enumeration for status.
    * @return array
    */
   public function mc_enum_etas() {
      try {
         return $this->_client->mc_enum_status($this->_login, $this->_password);
      } catch (SoapFault $e) {
         Toolbox::logInFile('mantis', sprintf(
               __('Error when getting MantisBT states => \'%1$s\'', 'mantis'),
               $e->getMessage()) . "\n");
      }
   }

   public function setClient($client) {
      $this->_client = $client;
   }

   public function getClient() {
      return $this->_client;
   }

   public function setHost($host) {
      $this->_host = $host;
   }

   public function getHost() {
      return $this->_host;
   }

   public function setLogin($login) {
      $this->_login = $login;
   }

   public function getLogin() {
      return $this->_login;
   }

   public function setPassword($password) {
      $this->_password = $password;
   }

   public function getPassword() {
      return $this->_password;
   }

   public function setUrl($url) {
      $this->_url = $url;
   }

   public function getUrl() {
      return $this->_url;
   }

}