<?php

include ('../../../inc/includes.php');
require_once('../inc/mantis.class.php');


    $mantis = new PluginMantisMantis();

    if(isset($_POST["escalade"])){

        //session::checkRight("Ticket","w");
        $mantis->exportGlpiIssueToMantisBT($_POST);
        Html::back();

    }
    Html::back();


