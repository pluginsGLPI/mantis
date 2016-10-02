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