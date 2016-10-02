<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015-2016 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMantisMantisws {

   private $_host;

   private $_url;

   private $_login;

   private $_password;

   private $_client;

   function __construct() {
   }

   function getConnexion($host, $url, $login, $pwd) {
      $this->_host = $host;
      $this->_url = $url;
      $this->_password = $pwd;
      $this->_login = $login;
      
      $this->_client = new SoapClient($this->_host . "/" . $this->_url);
   }

   /**
    * function to initialize the connection to the Web service
    * with the configuration settings stored in BDD
    */
   function initializeConnection() {
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);
      
      if (! empty($conf->fields["host"]) && ! empty($conf->fields["url"])) {
         $this->_host = $conf->fields["host"];
         $this->_url = $conf->fields["url"];
         $this->_login = $conf->fields["login"];
         $this->_password = Toolbox::decrypt($conf->fields["pwd"], GLPIKEY);
         
         $this->_client = new SoapClient($this->_host . "/" . $this->_url);
         return true;
      } else {
         return false;
      }
   }

   /**
    * function to test the connectivity of the web service
    *
    * @param $host
    * @param $url
    * @param $login
    * @param $password
    * @return bool
    * @throws Exception
    */
   function testConnectionWS($host, $url, $login, $password) {
      if (empty($host) or empty($url)) {
         return false;
      }
      
      try {
         $client = new SoapClient($host . "/" . $url);
         $client->mc_login($login, $password);
         return true;
      } catch ( SoapFault $sp ) {
         Toolbox::logInFile('mantis', sprintf(__('Error to connect to the web service MantisBT => \'%1$s\'', 'mantis'), $sp->getMessage()) . "\n");
         
         if ($sp->getMessage() == 'Access denied') {
            return false;
         } else {
            throw new Exception($sp->getMessage());
         }
      }
   }

   /**
    *
    * @param $name
    * @return array|bool
    */
   public function getActorFromProjectName($name) {
      $id = $this->getProjectIdWithName($name);
      try {
         $response = $this->_client->mc_project_get_users($this->_login, $this->_password, $id, array(
               90,
               10,
               25,
               40,
               55,
               70
         ));
         
         $list = array();
         $list[] = array(
               'id' => 0,
               'name' => '----'
         );
         foreach ($response as &$actor) {
            $list[] = array(
                  'id' => $actor->id,
                  'name' => $actor->name
            );
         }
         
         return ($list);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error retrieving user of project id \'%1$s\' => \'%2$s\'', 'mantis'), $id, $e->getMessage()) . "\n");
         return false;
      }
   }

   public function getCustomFieldFromProjectName($name) {
      $id = $this->getProjectIdWithName($name);
      try {
         $response = $this->_client->mc_project_get_custom_fields($this->_login, $this->_password, $id);
         
         $list = array();
         $list[] = array(
               'id' => 'additional_information',
               'name' => 'additional_information'
         );
         $list[] = array(
               'id' => 'note',
               'name' => 'note'
         );
         foreach ($response as $field) {
            $list[] = array(
                  'id' => $field->field->name,
                  'name' => $field->field->name
            );
         }
         
         return ($list);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error retrieving user of project id \'%1$s\' => \'%2$s\'', 'mantis'), $id, $e->getMessage()) . "\n");
         return false;
      }
   }

   public function getCustomFieldByNameAndProject($nameCustomField, $nameProject) {
      $id = $this->getProjectIdWithName($nameProject);
      try {
         $response = $this->_client->mc_project_get_custom_fields($this->_login, $this->_password, $id);
         
         $list = array();
         $list[] = array(
               'id' => 0,
               'name' => '----'
         );
         foreach ($response as $field) {
            if ($field->field->name == $nameCustomField) {
               return $field->field;
            }
         }
         
         return null;
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error retrieving user of project id \'%1$s\' => \'%2$s\'', 'mantis'), $id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to find category by name of project
    *
    * @param $name name of project
    * @return array return categorie if find else false
    */
   public function getCategoryFromProjectName($name) {
      $id = $this->getProjectIdWithName($name);
      try {
         $response = $this->_client->mc_project_get_categories($this->_login, $this->_password, $id);
         return ($response);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error retrieving category from the project id \'%1$s\' => \'%2$s\'', 'mantis'), $id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * function to check if an issue exist
    *
    * @param $_issue_id
    * @return bool
    */
   public function existIssueWithId($_issue_id) {
      try {
         $response = $this->_client->mc_issue_exists($this->_login, $this->_password, $_issue_id);
         return ($response);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(sprintf(__('Error when checking existence of the MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_id, $e->getMessage())) . "\n");
         
         return false;
      }
   }

   /**
    * Function to delete an issue with id
    *
    * @param integer $_issue_id
    * @return boolean
    */
   public function deleteIssue($_issue_id) {
      return $this->_client->mc_issue_delete($this->_login, $this->_password, $_issue_id);
   }

   /**
    * Method to call the operation originally named mc_issue_note_add
    *
    * @param integer $_issue_id
    * @param PluginMantisStructissuenotedata $_note
    * @return integer
    */
   public function addNoteToIssue($_issue_id, PluginMantisStructissuenotedata $_note) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_note_add($this->_login, $this->_password, $_issue_id, $_note);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('error while creating note => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to add an attachment to an issue
    *
    * @param integer $_issue_id
    * @param string $_name
    * @param string $_file_type
    * @param base64Binary $_content
    * @return integer
    */
   public function addAttachmentToIssue($_issue_id, $_name, $_file_type, $_content) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_attachment_add($this->_login, $this->_password, $_issue_id, $_name, $_file_type, $_content);
      } catch ( SoapFault $e ) {
         
         if ($e->getMessage() == "Duplicate filename.") {
            Toolbox::logInFile('mantis', __('WARNIG ' . $_name . ' already exist', 'mantis') . "\n");
            return true;
         } else {
            Toolbox::logInFile('mantis', sprintf(__('error while creating attachment => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
            return false;
         }
      }
   }

   /**
    * Function to get an attachment to an issue
    *
    * @param integer $_issue_id
    * @param string $_name
    * @param string $_file_type
    * @param base64Binary $_content
    * @return integer
    */
   public function getAttachmentFromIssue($_issue_id) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_attachment_get($this->_login, $this->_password, $_issue_id);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('error while getting attachment => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to add issue
    *
    * @param $issue
    * @return Integer
    */
   function addIssue($issue) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_add($this->_login, $this->_password, $issue);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error creating MantisBT ticket \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Function to find issue by id
    *
    * @param $idIssue
    * @return bool
    */
   function getIssueById($idIssue) {
      global $CFG_GLPI;
      try {
         $response = $this->_client->mc_issue_get($this->_login, $this->_password, $idIssue);
         return $response;
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error searching MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'), $idIssue, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * function to find id of project with name
    *
    * @param $name
    * @return mixed
    */
   public function getProjectIdWithName($name) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error retrieving the id of the project by it\'s name  \'%1$s\' => \'%2$s\'', 'mantis'), $name, $e->getMessage()) . "\n");
         return "ERROR -> " . $e->getMessage();
      }
   }

   /**
    * function to check if project exist (with name)
    *
    * @param $name
    * @return bool
    */
   public function existProjectWithName($name) {
      global $CFG_GLPI;
      try {
         $response = $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
         if ($response == 0)
            return false;
         else
            return true;
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error when checking the  existence of the project by his name \'%1$s\' => \'%2$s\'', 'mantis'), $name, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Delete the note with the specified id.
    *
    * @param integer $_issue_note_id
    * @return boolean
    */
   public function deleteNote($_issue_note_id) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_note_delete($this->_login, $this->_password, $_issue_note_id);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error when deleting note \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_note_id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Delete the issue attachment with the specified id.
    *
    * @param integer $_issue_attachment_id
    * @return boolean
    */
   public function deleteAttachment($_issue_attachment_id) {
      global $CFG_GLPI;
      try {
         return $this->_client->mc_issue_attachment_delete($this->_login, $this->_password, $_issue_attachment_id);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error when deleting attachment \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_attachment_id, $e->getMessage()) . "\n");
         return false;
      }
   }

   /**
    * Get the enumeration for status.
    *
    * @return array
    */
   public function getStateMantis() {
      try {
         return $this->_client->mc_enum_status($this->_login, $this->_password);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error when getting MantisBT states => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
      }
   }

   public function updateIssueMantis($id, $issue) {
      try {
         return $this->_client->mc_issue_update($this->_login, $this->_password, $id, $issue);
      } catch ( SoapFault $e ) {
         Toolbox::logInFile('mantis', sprintf(__('Error when updating MantisBT => \'%1$s\'', 'mantis'), $e->getMessage()) . "\n");
         return false;
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