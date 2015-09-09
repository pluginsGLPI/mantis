<?php
/**
 * File for class MantisStructCustomFieldValueForIssueData
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 * @date 2014-03-26
 */
/**
 * This class stands for MantisStructCustomFieldValueForIssueData originally named CustomFieldValueForIssueData
 * Meta informations extracted from the WSDL
 * - from schema : var/wsdltophp.com/storage/wsdls/a80caff3c8dd52f94a68432974b9ab45/wsdl.xml
 * 
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 *          @date 2014-03-26
 */
class PluginMantisStructcustomField {

   /**
    * The field
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var MantisStructObjectRef
    */
   public $field;

   /**
    * The value
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    * 
    * @var string
    */
   public $value;

   /**
    * Get field value
    * 
    * @return MantisStructObjectRef|null
    */
   public function getField() {
      return $this->field;
   }

   /**
    * Set field value
    * 
    * @param MantisStructObjectRef $_field the field
    * @return MantisStructObjectRef
    */
   public function setField($_field) {
      return ($this->field = $_field);
   }

   /**
    * Get value value
    * 
    * @return string|null
    */
   public function getValue() {
      return $this->value;
   }

   /**
    * Set value value
    * 
    * @param string $_value the value
    * @return string
    */
   public function setValue($_value) {
      return ($this->value = $_value);
   }
}