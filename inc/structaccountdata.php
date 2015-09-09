<?php
/**
 * File for class MantisStructAccountData
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 * @date 2014-03-26
 */
/**
 * This class stands for MantisStructAccountData originally named AccountData
 * Meta informations extracted from the WSDL
 * - from schema : var/wsdltophp.com/storage/wsdls/a80caff3c8dd52f94a68432974b9ab45/wsdl.xml
 * 
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 *          @date 2014-03-26
 *          PluginMantisStructissuenotedata
 */
class PluginMantisStructaccountdata {

   /**
    * The id
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var integer
    */
   public $id;

   /**
    * The name
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var string
    */
   public $name;

   /**
    * The real_name
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var string
    */
   public $real_name;

   /**
    * The email
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var string
    */
   public $email;

   /**
    * Constructor method for AccountData
    * 
    * @see parent::__construct()
    * @param integer $_id
    * @param string $_name
    * @param string $_real_name
    * @param string $_email
    * @return MantisStructAccountData
    */
   public function __construct($_id = NULL, $_name = NULL, $_real_name = NULL, $_email = NULL) {
   }

   /**
    * Get id value
    * 
    * @return integer|null
    */
   public function getId() {
      return $this->id;
   }

   /**
    * Set id value
    * 
    * @param integer $_id the id
    * @return integer
    */
   public function setId($_id) {
      return ($this->id = $_id);
   }

   /**
    * Get name value
    * 
    * @return string|null
    */
   public function getName() {
      return $this->name;
   }

   /**
    * Set name value
    * 
    * @param string $_name the name
    * @return string
    */
   public function setName($_name) {
      return ($this->name = $_name);
   }

   /**
    * Get real_name value
    * 
    * @return string|null
    */
   public function getReal_name() {
      return $this->real_name;
   }

   /**
    * Set real_name value
    * 
    * @param string $_real_name the real_name
    * @return string
    */
   public function setReal_name($_real_name) {
      return ($this->real_name = $_real_name);
   }

   /**
    * Get email value
    * 
    * @return string|null
    */
   public function getEmail() {
      return $this->email;
   }

   /**
    * Set email value
    * 
    * @param string $_email the email
    * @return string
    */
   public function setEmail($_email) {
      return ($this->email = $_email);
   }

   /**
    * Method called when an object has been exported with var_export() functions
    * It allows to return an object instantiated with the values
    * 
    * @see MantisWsdlClass::__set_state()
    * @uses MantisWsdlClass::__set_state()
    * @param array $_array the exported values
    * @return MantisStructAccountData
    */
   public static function __set_state(array $_array, $_className = __CLASS__) {
      return parent::__set_state($_array, $_className);
   }

   /**
    * Method returning the class name
    * 
    * @return string __CLASS__
    */
   public function __toString() {
      return __CLASS__;
   }
}
