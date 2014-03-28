<?php

/**
*function to define the version for glpi for plugin
*@return array
*/
function plugin_version_mantis(){

    return array ('name' 	       => 'Mantis',
                  'version' 	   => '1.0.0',
                  'author'         => 'Stanislas KITA (teclib\')',
                  'license'        => 'GPLv2+',
                  'homepage'       => 'http://www.teclib.com',
                  'minGlpiVersion' => '0.84');

}

/**
 * function to check the prerequisites
 * @return bool
 */
function plugin_mantis_check_prerequisites() {

    if (version_compare(GLPI_VERSION,'0.84','lt')
        || version_compare(GLPI_VERSION,'0.85','ge')) {
        echo "This plugin requires GLPI = 0.84";
        return false;
    }
    return true;
}


/**
 * function to check the initial configuration
 * @param boolean $verbose
 * @return boolean
 */
function plugin_mantis_check_config($verbose = false){

    if (true){
        //your configuration check
        return true;
    }

    if($verbose){
        echo 'Installed / not configured';
    }

    return false;
}

/**
 * function to initialize the plugin
 * @global array $PLUGIN_HOOKS
 */
function plugin_init_mantis(){

    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['mantis'] = true;
    $PLUGIN_HOOKS['menu_entry']['mantis'] = 'front/config.form.php';
    $PLUGIN_HOOKS['config_page']['mantis'] = 'front/config.form.php';
    $PLUGIN_HOOKS['add_javascript']['mantis'] = array('scripts/scriptMantis.js',
                                                      'scripts/jquery-1.11.0.min.js');



    Plugin::registerClass('PluginMantisChampsglpi');
    Plugin::registerClass('PluginMantisLinkfield');
    Plugin::registerClass('PluginMantisChampsmantisbt');
    Plugin::registerClass('PluginMantisProfile');
    Plugin::registerClass('PluginMantisConfig');
    Plugin::registerClass('PluginMantisMantisws');
    Plugin::registerClass('PluginMantisMantis', array('addtabon' => array('Ticket')));


}

function plugin_mantis_haveRight($a,$b){
    return true;
}
