<?php
/**
 * File for class MantisStructProjectAttachmentData
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 * @date 2014-03-26
 */
/**
 * This class stands for MantisStructProjectAttachmentData originally named ProjectAttachmentData
 * Meta informations extracted from the WSDL
 * - from schema : var/wsdltophp.com/storage/wsdls/a80caff3c8dd52f94a68432974b9ab45/wsdl.xml
 * @package Mantis
 * @subpackage Structs
 * @author WsdlToPhp Team <contact@wsdltophp.fr>
 * @version 20140325-01
 * @date 2014-03-26
 */
class PluginMantisProjectAttachment
{
    /**
     * The id
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var integer
     */
    public $id;
    /**
     * The filename
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var string
     */
    public $filename;
    /**
     * The title
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var string
     */
    public $title;
    /**
     * The description
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var string
     */
    public $description;
    /**
     * The size
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var integer
     */
    public $size;
    /**
     * The content_type
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var string
     */
    public $content_type;
    /**
     * The date_submitted
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var dateTime
     */
    public $date_submitted;
    /**
     * The download_url
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var anyURI
     */
    public $download_url;
    /**
     * The user_id
     * Meta informations extracted from the WSDL
     * - minOccurs : 0
     * @var integer
     */
    public $user_id;
    /**
     * Constructor method for ProjectAttachmentData
     * @see parent::__construct()
     * @param integer $_id
     * @param string $_filename
     * @param string $_title
     * @param string $_description
     * @param integer $_size
     * @param string $_content_type
     * @param dateTime $_date_submitted
     * @param anyURI $_download_url
     * @param integer $_user_id
     * @return MantisStructProjectAttachmentData
     */
    public function __construct(){

    }
    /**
     * Get id value
     * @return integer|null
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set id value
     * @param integer $_id the id
     * @return integer
     */
    public function setId($_id)
    {
        return ($this->id = $_id);
    }
    /**
     * Get filename value
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }
    /**
     * Set filename value
     * @param string $_filename the filename
     * @return string
     */
    public function setFilename($_filename)
    {
        return ($this->filename = $_filename);
    }
    /**
     * Get title value
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Set title value
     * @param string $_title the title
     * @return string
     */
    public function setTitle($_title)
    {
        return ($this->title = $_title);
    }
    /**
     * Get description value
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Set description value
     * @param string $_description the description
     * @return string
     */
    public function setDescription($_description)
    {
        return ($this->description = $_description);
    }
    /**
     * Get size value
     * @return integer|null
     */
    public function getSize()
    {
        return $this->size;
    }
    /**
     * Set size value
     * @param integer $_size the size
     * @return integer
     */
    public function setSize($_size)
    {
        return ($this->size = $_size);
    }
    /**
     * Get content_type value
     * @return string|null
     */
    public function getContent_type()
    {
        return $this->content_type;
    }
    /**
     * Set content_type value
     * @param string $_content_type the content_type
     * @return string
     */
    public function setContent_type($_content_type)
    {
        return ($this->content_type = $_content_type);
    }
    /**
     * Get date_submitted value
     * @return dateTime|null
     */
    public function getDate_submitted()
    {
        return $this->date_submitted;
    }
    /**
     * Set date_submitted value
     * @param dateTime $_date_submitted the date_submitted
     * @return dateTime
     */
    public function setDate_submitted($_date_submitted)
    {
        return ($this->date_submitted = $_date_submitted);
    }
    /**
     * Get download_url value
     * @return anyURI|null
     */
    public function getDownload_url()
    {
        return $this->download_url;
    }
    /**
     * Set download_url value
     * @param anyURI $_download_url the download_url
     * @return anyURI
     */
    public function setDownload_url($_download_url)
    {
        return ($this->download_url = $_download_url);
    }
    /**
     * Get user_id value
     * @return integer|null
     */
    public function getUser_id()
    {
        return $this->user_id;
    }
    /**
     * Set user_id value
     * @param integer $_user_id the user_id
     * @return integer
     */
    public function setUser_id($_user_id)
    {
        return ($this->user_id = $_user_id);
    }
    /**
     * Method called when an object has been exported with var_export() functions
     * It allows to return an object instantiated with the values
     * @see MantisWsdlClass::__set_state()
     * @uses MantisWsdlClass::__set_state()
     * @param array $_array the exported values
     * @return MantisStructProjectAttachmentData
     */
    public static function __set_state(array $_array,$_className = __CLASS__)
    {
        return parent::__set_state($_array,$_className);
    }
    /**
     * Method returning the class name
     * @return string __CLASS__
     */
    public function __toString()
    {
        return __CLASS__;
    }
}
