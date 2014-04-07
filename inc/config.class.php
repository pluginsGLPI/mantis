<?php
require_once('mantisIssue.class.php');
/**
 * Class PluginMantisConfig pour la partie gestion de la configuration
 */
class PluginMantisConfig extends CommonDBTM
{


    /**
     * Function to define if the user have right to create
     * @return bool|booleen
     */
    static function canCreate()
    {
        return Session::haveRight('config', 'w');
    }


    /**
     * Function to define if the user have right to view
     * @return bool|booleen
     */
    static function canView()
    {
        return Session::haveRight('config', 'r');
    }


    /**
     * Function to show form for configurate the plugin Mantis
     */
    function showConfigForm()
    {
        $this->showConfigWS();
    }


    /**
     * Function to display form to configure
     * connection to WS MantisBT
     */
    function showConfigWS()
    {
        //we recover the first and only record
        $this->getFromDB(1);

        echo "<form method='post' action='./config.form.php' method='post'>";
        echo "<table class='tab_cadre' cellpadding='5'>";
        echo "<tr><th colspan='6'>".__("Mantis plugin setup","mantis")."</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("MantisBT serveur IP","mantis")."</td>";
        echo "<td><input id='host' type='text' name='host' onblur='testIP();' value='" . $this->fields["host"] . "'/></td>";
        echo "<td>ex: 128.65.25.74</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("Wsdl file path","mantis")."</td>";
        echo "<td><input id='url' type='text' name='url' value='" . $this->fields["url"] . "'/></td>";
        echo "<td>ex: mantis/api/soap/mantisconnect.php?wsdl</td></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("MantisBT user login","mantis")."</td>";
        echo "<td><input  id='login' type='text' name='login' value='" . $this->fields["login"] . "'/></td>";
        echo "<td></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("MantisBT user password","mantis")."</td>";
        echo "<td><input  id='pwd' type='password' name='pwd' value='" . $this->fields["pwd"] . "'/></td>";
        echo "<td></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("MantisBT field for the link to the ticket GLPI","mantis")."</td>";
        echo "<td>";
        DropDown::showFromArray('champsUrlGlpi', PluginMantisIssue::$champsMantis, array('value' => $this->fields["champsUrlGlpi"]));
        echo "</td>";
        echo "<td></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__("MantisBT field for GLPI fields</br>(title, description,category, followed, tasks)","mantis")."</td>";
        echo "<td>";
        DropDown::showFromArray('champsGlpi', PluginMantisIssue::$champsMantis, array('value' => $this->fields["champsGlpi"]));
        echo "</td>";
        echo "<td></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><input type='hidden' name='id' value='1' class='submit'>";
        echo "<input id='update' type='submit' name='update' value='".__("Update","mantis")."' class='submit'></td>";
        echo "<td><input id='test' onclick='testConnexionMantisWS();' name='test' value='".__("Test the connection","mantis")."'class='submit'></td>";
        echo "<td><div id='infoAjax'></div></td></tr>";

        echo "</table>";
        Html::closeForm();

    }

}