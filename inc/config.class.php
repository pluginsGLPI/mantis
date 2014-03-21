<?php

/**
 * Class PluginMantisConfig pour la partie gestion de la configuration
 */
class PluginMantisConfig extends CommonDBTM {

    static private $_instance = NULL;



    static function getInstance() {

        if (!isset(self::$_instance)) {
            self::$_instance = new self();
            if (!self::$_instance->getFromDB(1)) {
                self::$_instance->getEmpty();
            }
        }
        return self::$_instance;
    }


    /**
     * Affiche le formulaire de configuration du plugin
     * @param $item
     * @return boolean
     */
    static function showConfigForm($item) {

        $config = self::getInstance();

        $config->showFormHeader();


        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("URI du fichier WSDL de MantisBT", "mantis")."</td><td>";
        echo "<input type='text' name='mantis_url_wsdl' value='' autocomplete='off'>";
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("Login", "mantis")."</td><td>";
        echo "<input type='text' name='mantis_user_login' value='' autocomplete='off'>";
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("Password", "mantis")."</td><td>";
        echo "<input type='password' name='mantis_user_password' value='' autocomplete='off'>";
        echo "</td></tr>\n";

        $config->showFormButtons(array('candel'=>false));
        return false;

    }



}