<?php

/**
*function to define the version for glpi for plugin
*@return type
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
 * @param type $verbose
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
    $PLUGIN_HOOKS['config_page']['mantis'] = 'front/config.form.php';

}