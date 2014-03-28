<?php


class PluginMantisIssue {


    public static $champUrlGlpi = array('none'=>'----','note'=> 'note','additional_information' => 'additional_information');


    public $id;
    public $view_state;
    public $last_updated;
    public $project;
    public $category;
    public $priority;
    public $severity;
    public $status;
    public $reporter;
    public $summary;
    public $version;
    public $build;
    public $platform;
    public $os;
    public $os_build;
    public $reproducibility;
    public $date_submitted;
    public $sponsorship_total;
    public $handler;
    public $projection;
    public $eta;
    public $resolution;
    public $fixed_in_version;
    public $target_version;
    public $description;
    public $steps_to_reproduce;
    public $additional_information;
    public $attachments;
    public $relationships;
    public $notes;
    public $custom_fields;
    public $due_date;
    public $monitors;
    public $sticky;
    public $tags;


    /**
     * Constructor method for IssueData
     * @see parent::__construct()
     *
     * @param MantisStructAccountData $_reporter
     * @param MantisStructAccountData $_handler
     *
     * @param MantisStructObjectRef $_view_state
     * @param MantisStructObjectRef $_priority
     * @param MantisStructObjectRef $_severity
     * @param MantisStructObjectRef $_status
     * @param MantisStructObjectRef $_project
     * @param MantisStructObjectRef $_reproducibility
     * @param MantisStructObjectRef $_projection
     * @param MantisStructObjectRef $_eta
     * @param MantisStructObjectRef $_resolution
     *
     * @param integer $_id
     * @param dateTime $_last_updated
     * @param string $_category
     * @param string $_summary
     * @param string $_version
     * @param string $_build
     * @param string $_platform
     * @param string $_os
     * @param string $_os_build
     * @param dateTime $_date_submitted
     * @param integer $_sponsorship_total
     * @param string $_fixed_in_version
     * @param string $_target_version
     * @param string $_description
     * @param string $_steps_to_reproduce
     * @param string $_additional_information
     * @param Array $_attachments
     * @param Array $_relationships
     * @param Array $_notes
     * @param Array $_custom_fields
     * @param dateTime $_due_date
     * @param Array $_monitors
     * @param boolean $_sticky
     * @param Array $_tags
     * @return MantisStructIssueData
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
     * Get view_state value
     * @return MantisStructObjectRef|null
     */
    public function getView_state()
    {
        return $this->view_state;
    }
    /**
     * Set view_state value
     * @param MantisStructObjectRef $_view_state the view_state
     * @return MantisStructObjectRef
     */
    public function setView_state($_view_state)
    {
        return ($this->view_state = $_view_state);
    }
    /**
     * Get last_updated value
     * @return dateTime|null
     */
    public function getLast_updated()
    {
        return $this->last_updated;
    }
    /**
     * Set last_updated value
     * @param dateTime $_last_updated the last_updated
     * @return dateTime
     */
    public function setLast_updated($_last_updated)
    {
        return ($this->last_updated = $_last_updated);
    }
    /**
     * Get project value
     * @return MantisStructObjectRef|null
     */
    public function getProject()
    {
        return $this->project;
    }
    /**
     * Set project value
     * @param MantisStructObjectRef $_project the project
     * @return MantisStructObjectRef
     */
    public function setProject($_project)
    {
        return ($this->project = $_project);
    }
    /**
     * Get category value
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * Set category value
     * @param string $_category the category
     * @return string
     */
    public function setCategory($_category)
    {
        return ($this->category = $_category);
    }
    /**
     * Get priority value
     * @return MantisStructObjectRef|null
     */
    public function getPriority()
    {
        return $this->priority;
    }
    /**
     * Set priority value
     * @param MantisStructObjectRef $_priority the priority
     * @return MantisStructObjectRef
     */
    public function setPriority($_priority)
    {
        return ($this->priority = $_priority);
    }
    /**
     * Get severity value
     * @return MantisStructObjectRef|null
     */
    public function getSeverity()
    {
        return $this->severity;
    }
    /**
     * Set severity value
     * @param MantisStructObjectRef $_severity the severity
     * @return MantisStructObjectRef
     */
    public function setSeverity($_severity)
    {
        return ($this->severity = $_severity);
    }
    /**
     * Get status value
     * @return MantisStructObjectRef|null
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * Set status value
     * @param MantisStructObjectRef $_status the status
     * @return MantisStructObjectRef
     */
    public function setStatus($_status)
    {
        return ($this->status = $_status);
    }
    /**
     * Get reporter value
     * @return MantisStructAccountData|null
     */
    public function getReporter()
    {
        return $this->reporter;
    }
    /**
     * Set reporter value
     * @param MantisStructAccountData $_reporter the reporter
     * @return MantisStructAccountData
     */
    public function setReporter($_reporter)
    {
        return ($this->reporter = $_reporter);
    }
    /**
     * Get summary value
     * @return string|null
     */
    public function getSummary()
    {
        return $this->summary;
    }
    /**
     * Set summary value
     * @param string $_summary the summary
     * @return string
     */
    public function setSummary($_summary)
    {
        return ($this->summary = $_summary);
    }
    /**
     * Get version value
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }
    /**
     * Set version value
     * @param string $_version the version
     * @return string
     */
    public function setVersion($_version)
    {
        return ($this->version = $_version);
    }
    /**
     * Get build value
     * @return string|null
     */
    public function getBuild()
    {
        return $this->build;
    }
    /**
     * Set build value
     * @param string $_build the build
     * @return string
     */
    public function setBuild($_build)
    {
        return ($this->build = $_build);
    }
    /**
     * Get platform value
     * @return string|null
     */
    public function getPlatform()
    {
        return $this->platform;
    }
    /**
     * Set platform value
     * @param string $_platform the platform
     * @return string
     */
    public function setPlatform($_platform)
    {
        return ($this->platform = $_platform);
    }
    /**
     * Get os value
     * @return string|null
     */
    public function getOs()
    {
        return $this->os;
    }
    /**
     * Set os value
     * @param string $_os the os
     * @return string
     */
    public function setOs($_os)
    {
        return ($this->os = $_os);
    }
    /**
     * Get os_build value
     * @return string|null
     */
    public function getOs_build()
    {
        return $this->os_build;
    }
    /**
     * Set os_build value
     * @param string $_os_build the os_build
     * @return string
     */
    public function setOs_build($_os_build)
    {
        return ($this->os_build = $_os_build);
    }
    /**
     * Get reproducibility value
     * @return MantisStructObjectRef|null
     */
    public function getReproducibility()
    {
        return $this->reproducibility;
    }
    /**
     * Set reproducibility value
     * @param MantisStructObjectRef $_reproducibility the reproducibility
     * @return MantisStructObjectRef
     */
    public function setReproducibility($_reproducibility)
    {
        return ($this->reproducibility = $_reproducibility);
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
     * Get sponsorship_total value
     * @return integer|null
     */
    public function getSponsorship_total()
    {
        return $this->sponsorship_total;
    }
    /**
     * Set sponsorship_total value
     * @param integer $_sponsorship_total the sponsorship_total
     * @return integer
     */
    public function setSponsorship_total($_sponsorship_total)
    {
        return ($this->sponsorship_total = $_sponsorship_total);
    }
    /**
     * Get handler value
     * @return MantisStructAccountData|null
     */
    public function getHandler()
    {
        return $this->handler;
    }
    /**
     * Set handler value
     * @param MantisStructAccountData $_handler the handler
     * @return MantisStructAccountData
     */
    public function setHandler($_handler)
    {
        return ($this->handler = $_handler);
    }
    /**
     * Get projection value
     * @return MantisStructObjectRef|null
     */
    public function getProjection()
    {
        return $this->projection;
    }
    /**
     * Set projection value
     * @param MantisStructObjectRef $_projection the projection
     * @return MantisStructObjectRef
     */
    public function setProjection($_projection)
    {
        return ($this->projection = $_projection);
    }
    /**
     * Get eta value
     * @return MantisStructObjectRef|null
     */
    public function getEta()
    {
        return $this->eta;
    }
    /**
     * Set eta value
     * @param MantisStructObjectRef $_eta the eta
     * @return MantisStructObjectRef
     */
    public function setEta($_eta)
    {
        return ($this->eta = $_eta);
    }
    /**
     * Get resolution value
     * @return MantisStructObjectRef|null
     */
    public function getResolution()
    {
        return $this->resolution;
    }
    /**
     * Set resolution value
     * @param MantisStructObjectRef $_resolution the resolution
     * @return MantisStructObjectRef
     */
    public function setResolution($_resolution)
    {
        return ($this->resolution = $_resolution);
    }
    /**
     * Get fixed_in_version value
     * @return string|null
     */
    public function getFixed_in_version()
    {
        return $this->fixed_in_version;
    }
    /**
     * Set fixed_in_version value
     * @param string $_fixed_in_version the fixed_in_version
     * @return string
     */
    public function setFixed_in_version($_fixed_in_version)
    {
        return ($this->fixed_in_version = $_fixed_in_version);
    }
    /**
     * Get target_version value
     * @return string|null
     */
    public function getTarget_version()
    {
        return $this->target_version;
    }
    /**
     * Set target_version value
     * @param string $_target_version the target_version
     * @return string
     */
    public function setTarget_version($_target_version)
    {
        return ($this->target_version = $_target_version);
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
     * Get steps_to_reproduce value
     * @return string|null
     */
    public function getSteps_to_reproduce()
    {
        return $this->steps_to_reproduce;
    }
    /**
     * Set steps_to_reproduce value
     * @param string $_steps_to_reproduce the steps_to_reproduce
     * @return string
     */
    public function setSteps_to_reproduce($_steps_to_reproduce)
    {
        return ($this->steps_to_reproduce = $_steps_to_reproduce);
    }
    /**
     * Get additional_information value
     * @return string|null
     */
    public function getAdditional_information()
    {
        return $this->additional_information;
    }
    /**
     * Set additional_information value
     * @param string $_additional_information the additional_information
     * @return string
     */
    public function setAdditional_information($_additional_information)
    {
        return ($this->additional_information = $_additional_information);
    }
    /**
     * Get attachments value
     * @return Array|null
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
    /**
     * Set attachments value
     * @param Array $_attachments the attachments
     * @return Array
     */
    public function setAttachments($_attachments)
    {
        return ($this->attachments = $_attachments);
    }
    /**
     * Get relationships value
     * @return Array|null
     */
    public function getRelationships()
    {
        return $this->relationships;
    }
    /**
     * Set relationships value
     * @param Array $_relationships the relationships
     * @return Array
     */
    public function setRelationships($_relationships)
    {
        return ($this->relationships = $_relationships);
    }
    /**
     * Get notes value
     * @return Array|null
     */
    public function getNotes()
    {
        return $this->notes;
    }
    /**
     * Set notes value
     * @param Array $_notes the notes
     * @return Array
     */
    public function setNotes($_notes)
    {
        return ($this->notes = $_notes);
    }
    /**
     * Get custom_fields value
     * @return Array|null
     */
    public function getCustom_fields()
    {
        return $this->custom_fields;
    }
    /**
     * Set custom_fields value
     * @param Array $_custom_fields the custom_fields
     * @return Array
     */
    public function setCustom_fields($_custom_fields)
    {
        return ($this->custom_fields = $_custom_fields);
    }
    /**
     * Get due_date value
     * @return dateTime|null
     */
    public function getDue_date()
    {
        return $this->due_date;
    }
    /**
     * Set due_date value
     * @param dateTime $_due_date the due_date
     * @return dateTime
     */
    public function setDue_date($_due_date)
    {
        return ($this->due_date = $_due_date);
    }
    /**
     * Get monitors value
     * @return Array|null
     */
    public function getMonitors()
    {
        return $this->monitors;
    }
    /**
     * Set monitors value
     * @param Array $_monitors the monitors
     * @return Array
     */
    public function setMonitors($_monitors)
    {
        return ($this->monitors = $_monitors);
    }
    /**
     * Get sticky value
     * @return boolean|null
     */
    public function getSticky()
    {
        return $this->sticky;
    }
    /**
     * Set sticky value
     * @param boolean $_sticky the sticky
     * @return boolean
     */
    public function setSticky($_sticky)
    {
        return ($this->sticky = $_sticky);
    }
    /**
     * Get tags value
     * @return Array|null
     */
    public function getTags()
    {
        return $this->tags;
    }
    /**
     * Set tags value
     * @param Array $_tags the tags
     * @return Array
     */
    public function setTags($_tags)
    {
        return ($this->tags = $_tags);
    }
    /**
     * Method called when an object has been exported with var_export() functions
     * It allows to return an object instantiated with the values
     * @see MantisWsdlClass::__set_state()
     * @uses MantisWsdlClass::__set_state()
     * @param array $_array the exported values
     * @return MantisStructIssueData
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
