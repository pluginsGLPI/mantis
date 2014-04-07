<?php
include ('../../../inc/includes.php');
require_once('../inc/config.class.php');

Html::header(__("Setup - Mantis","mantis"), $_SERVER['PHP_SELF'],
    'plugins', 'Mantis', 'configuration');

$plugin = new Plugin();

if($plugin->isActivated('mantis')){
    $config = new PluginMantisConfig();
    if(isset($_POST['update'])){
        session::checkRight('config','w');
        $config->update($_POST);
        Html::back();
    }else{
        $config->showConfigForm();
    }
}else{
    global $CFG_GLPI;
    echo '<div class=\'center\'><br><br><img src=\''.$CFG_GLPI['root_doc'].'/pics/warning.png\' alt=\'warning\'><br><br>';
    echo '<b>'.__("thank you to activate plugin","mantis").'</b></div>';
}

Html::footer();