<?php

/**
 * -------------------------------------------------------------------------
 * Mantis plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Mantis.
 *
 * Mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mantis. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2014-2022 by Mantis plugin team.
 * @license   AGPLv3 https://www.gnu.org/licenses/agpl-3.0.html
 * @link      https://github.com/pluginsGLPI/mantis
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMantisStructaccountdata {

   /**
    * The id
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var integer
    */
   public $id;

   /**
    * The name
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $name;

   /**
    * The real_name
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $real_name;

   /**
    * The email
    * Meta information extracted from the WSDL
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
   public function __construct($_id = null, $_name = null, $_real_name = null, $_email = null) {
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
    * Method returning the class name
    *
    * @return string __CLASS__
    */
   public function __toString() {
      return __CLASS__;
   }
}
