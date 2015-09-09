<?php
/**
 * Created by PhpStorm.
 * User: stanislas
 * Date: 10/10/14
 * Time: 09:55
 */
class PluginMantisUserpref extends CommonDBTM {

   /**
    * Define tab name
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (in_array($item->getType(), array (
            'User',
            'Preference' 
      ))) {
         return __("MantisBT", "mantis");
      }
      return '';
   }

   static function getTypeName($nb = 0) {
      return __("MantisBT", "mantis");
   }

   static function canCreate() {
      return Session::haveRight('ticket', 'w');
   }

   static function canView() {
      return Session::haveRight('ticket', 'r');
   }

   /**
    * Define tab content
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'User') {
         $ID = $item->getField('id');
      } else if ($item->getType() == 'Preference') {
         $ID = Session::getLoginUserID();
      }
      
      $self = new self();
      $self->showForm($ID);
      
      return true;
   }

   /**
    * Function to show the form of plugin
    *
    * @param $item
    */
   public function showForm($ID, $options = array()) {
      if (! $this->getFromDB($ID)) {
         $this->add(array (
               'users_id' => $ID,
               'id' => $ID 
         ));
      }
      
      $target = $this->getFormURL();
      if (isset($options ['target'])) {
         $target = $options ['target'];
      }
      
      echo "<form method='post' action='" . $target . "' method='post'>";
      echo "<table id='table2' class='tab_cadre_fixe' cellpadding='2'>";
      echo "<tr class='headerRow'><th colspan='2'>" . __("Checkbox checked by default", "mantis") . "</th></tr>";
      
      // FOLLOW ATTACHMENT
      $checked = ($this->fields ['followAttachment']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Attachments", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followAttachment' id='followAttachment' " . $checked . ">" . __("To forward attachments", "mantis") . "<div id='attachmentforLinkToProject' ><div/></td></tr>";
      
      // FOLLOW GLPI FOLLOW
      $checked = ($this->fields ['followFollow']) ? "checked" : "";
      echo "<tr class='tab_bg_1' >";
      echo "<th>" . __("Glpi follow", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followFollow' id='followFollow' " . $checked . ">" . __("To forward follow", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI TASK
      $checked = ($this->fields ['followTask']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Glpi task", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followTask' id='followTask' " . $checked . ">" . __("To forward task", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI TITLE
      $checked = ($this->fields ['followTitle']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Glpi title", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followTitle' id='followTitle' " . $checked . ">" . __("To forward title", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI DEXCRIPTION
      $checked = ($this->fields ['followDescription']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Glpi description", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followDescription' id='followDescription' " . $checked . ">" . __("To forward description", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI CATEGORIE
      $checked = ($this->fields ['followCategorie']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Glpi categorie", "mantis") . "</th>";
      echo "<td><INPUT type='checkbox' name='followCategorie' id='followCategorie' " . $checked . ">" . __("To forward categorie", "mantis") . "</td></tr>";
      
      // FOLLOW GLPI LINKED
      $checked = ($this->fields ['followLinkedItem']) ? "checked" : "";
      echo "<tr class='tab_bg_1' >";
      echo "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
      echo "<td><INPUT type='checkbox' name='followLinkedItem' id='followLinkedItem' " . $checked . ">" . __("To forward linked Ticket", "mantis") . "</td></tr>";
      
      // INPUT BUTTON
      echo "<tr class='tab_bg_1'>";
      echo "<td><input id='update' type='submit' name='update' value='" . __("Update", "mantis") . "' class='submit'></td><td></td></tr>";
      echo "<input type='hidden' name='id' value=" . $this->fields ["id"] . ">";
      echo "<input type='hidden' name='users_id' value=" . $this->fields ["users_id"] . ">";
      
      echo "</table>";
      Html::closeForm();
   }
} 