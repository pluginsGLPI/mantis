<?php


class PluginMantisMantisws {

    private $_host;
    private $_url;
    private $_login;
    private $_password;
    private $_client;

    function __construct(){

    }


    /**
     * function to initialize the connection to the Web service
     * with the configuration settings stored in BDD
     */
    function initializeConnection(){
        $conf = new PluginMantisConfig();
        $conf->getFromDB(1);

        $this->_host = $conf->fields["host"];
        $this->_url = $conf->fields["url"];
        $this->_login = $conf->fields["login"];
        $this->_password = $conf->fields["pwd"];

        $this->_client =  new SoapClient("http://".$this->_host."/".$this->_url);
    }




    /**
     * function to test the connectivity of the web service
     * @return bool
     */
    function testConnectionWS($host, $url, $login, $password){

        $client = new SoapClient("http://".$host."/".$url);

        try{
            $client->mc_version();
            return true;
        }catch (Exception $e){
            return $e->getMessage();
        }

    }







    function getProject($client){

        try {

            $response = $client->mc_project_get_issues($this->_login,$this->_password, 1 , 1 , 10);
            var_dump($response);

        }catch (SoapFault $e){
            echo"ERROR -> ".$e->getMessage();

            echo"ERROR1 -> ".$e->getTraceAsString();
        }

    }



    function getFunction(){
        try {
            var_dump($this->_client->__getFunctions());
        }catch (SoapFault $e){
            echo"ERROR -> ".$e->getMessage();
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