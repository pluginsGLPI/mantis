<?php


class PluginMantisMantisws
{

    private $_host;
    private $_url;
    private $_login;
    private $_password;
    private $_client;

    function __construct()
    {

    }


    /**
     * function to initialize the connection to the Web service
     * with the configuration settings stored in BDD
     */
    function initializeConnection()
    {
        require_once('../inc/config.class.php');
        $conf = new PluginMantisConfig();
        $conf->getFromDB(1);

        $this->_host = $conf->fields["host"];
        $this->_url = $conf->fields["url"];
        $this->_login = $conf->fields["login"];
        $this->_password = $conf->fields["pwd"];

        $this->_client = new SoapClient("http://" . $this->_host . "/" . $this->_url);
    }


   /**
    * function to test the connectivity of the web service
    * @param $host
    * @param $url
    * @param $login
    * @param $password
    * @return bool
    */
   function testConnectionWS($host, $url, $login, $password) {
      try {
         $client = new SoapClient("http://" . $host . "/" . $url);
         $client->mc_project_get_issues($login, $password, 1, 1, 10);
         return true;
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error to connect to the web service MantisBT => \'%1$s\'', 'mantis'),$e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
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
         error_log(sprintf(__('Error retrieving category from the project id \'%1$s\' => \'%2$s\'', 'mantis'), $id, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
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
         error_log(sprintf(__('Error when checking existence of the MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_id, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
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
         error_log(sprintf(__('Error while deleting the ticket \'%1$s\' => \'%2$s\'', 'mantis'),$_issue_id, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
      }
   }


   /**
    * Method to call the operation originally named mc_issue_note_add
    * @param integer $_issue_id
    * @param PluginMantisStructIssueNoteData $_note
    * @return integer
    */
   public function addNoteToIssue($_issue_id, PluginMantisStructIssueNoteData $_note) {
      try {
         return $this->_client->mc_issue_note_add($this->_login, $this->_password, $_issue_id, $_note);
      } catch (SoapFault $e) {
         error_log(sprintf(__('error while creating note => \'%1$s\'', 'mantis'),$e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
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
      try {
         return $this->_client->mc_issue_attachment_add($this->_login, $this->_password, $_issue_id, $_name, $_file_type, $_content);
      } catch (SoapFault $e) {
         error_log(sprintf(__('error while creating attachment => \'%1$s\'', 'mantis'),$e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
      }
   }


   /**
    * Function to add issue
    * @param $issue
    * @return Integer
    */
   function addIssue($issue) {
      try {
         return $this->_client->mc_issue_add($this->_login, $this->_password, $issue);
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error creating MantisBT ticket \'%1$s\'', 'mantis'), $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
      }
   }


   /**
    * Function to find issue by id
    * @param $idIssue
    * @return bool
    */
   function getIssueById($idIssue) {
      try {
         $response = $this->_client->mc_issue_get($this->_login, $this->_password, $idIssue);
         return $response;
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error searching MantisBT ticket \'%1$s\' => \'%2$s\'', 'mantis'), $idIssue, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
      }
   }

   /**
    * function to find id of project with name
    * @param $name
    * @return mixed
    */
   public function getProjectIdWithName($name) {
      try {
         return $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error retrieving the id of the project by it\'s name  \'%1$s\' => \'%2$s\'', 'mantis'), $name, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         echo "ERROR -> " . $e->getMessage();
      }
   }


   /**
    * function to check if project exist (with name)
    * @param $name
    * @return bool
    */
   public function existProjectWithName($name) {
      try {
         $response = $this->_client->mc_project_get_id_from_name($this->_login, $this->_password, $name);
         if ($response == 0) return false;
         else return true;
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error when checking the  existence of the project by his name \'%1$s\' => \'%2$s\'', 'mantis'), $name, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
         return false;
      }
   }


   /**
    * Delete the note with the specified id.
    * @param integer $_issue_note_id
    * @return boolean
    */
   public function deleteNote($_issue_note_id) {
      try {
         return $this->_client->mc_issue_note_delete($this->_login, $this->_password, $_issue_note_id);
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error when deleting note \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_note_id, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
      }
   }


   /**
    * Delete the issue attachment with the specified id.
    * @param integer $_issue_attachment_id
    * @return boolean
    */
   public function deleteAttachment($_issue_attachment_id) {
      try {
         return $this->_client->mc_issue_attachment_delete($this->_login, $this->_password, $_issue_attachment_id);
      } catch (SoapFault $e) {
         error_log(sprintf(__('Error when deleting attachment \'%1$s\' => \'%2$s\'', 'mantis'), $_issue_attachment_id, $e->getMessage()) . "\n", 3, GLPI_ROOT . "/files/_log/mantis.log");
      }
   }


   /**
    * Get the value for the specified configuration variable.
    * @param string $_config_var
    * @return string
    */
   public function mc_config_get_string($_config_var) {
      try {
         return $this->_client->mc_config_get_string($this->_login, $this->_password, $_config_var);
      } catch (SoapFault $soapFault) {
         error_log(__("Error when loading the configuration", "mantis") . "\n", 3,GLPI_ROOT . "/files/_log/mantis.log");
      }
   }

   /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->_client = $client;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->_client;
    }


    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->_url;
    }

}