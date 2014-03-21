<?php

include ('../../../inc/includes.php');
require_once('../inc/config.class.php');

Session::checkRight("config","w");


Html::header("Configuration de MantisBT", $_SERVER["PHP_SELF"],"plugins", "mantis", "configuration");

$PluginMantisConfig = new PluginMantisConfig();
$PluginMantisConfig->showConfigForm($PluginMantisConfig);

Html::footer();