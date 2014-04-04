<?php

include('../../../inc/includes.php');
require_once('../inc/mantisWS.class.php');
require_once('../inc/mantis.class.php');
require_once('../inc/mantisIssue.class.php');


if (isset($_POST['action'])) {

    switch ($_POST['action']) {

        case 'testConnexionMantisWS':
            $ws = new PluginMantisMantisws();
            echo $ws->testConnectionWS($_POST['host'], $_POST['url'], $_POST['login'], $_POST['pwd']);
            break;

        case 'findIssueById':
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();
            echo $ws->existIssueWithId($_POST['id']);
            break;

        case 'findProjectByName':
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();
            echo $ws->existProjectWithName($_POST['name']);
            break;

        case 'getCategoryFromProjectName':
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();
            $result = $ws->getCategoryFromProjectName($_POST['name']);
            if (!$result) echo false;
            else echo json_encode($result);
            break;

        case 'LinkIssueGlpiToIssueMantis':

            $id_ticket = $_POST['idTicket'];
            $id_mantis_issue = $_POST['idMantis'];
            $ws = new PluginMantisMantisws();
            $ws->initializeConnection();

            //on verifie que l'id du ticket mantis existe
            if (!$ws->existIssueWithId($id_mantis_issue)) {
                echo 'L\'issue Mantis numéro : ' . $id_mantis_issue . ' n\'éxiste pas';
            } else {
                $mantis = new PluginMantisMantis();
                //on verifie si un lien est deja creé
                if ($mantis->IfExistLink($id_ticket, $id_mantis_issue)) {
                    echo 'Le ticket GLPI n° ' . $id_ticket . ' est déjà lié au ticket Mantis n° ' . $id_mantis_issue;
                } else {
                    $mantis->add($_POST);
                    echo true;
                }
            }
            break;


        case 'LinkIssueGlpiToProjectMantis':
            $issue = new PluginMantisIssue();
            echo $issue->linkisuetoProjectMantis($_POST);

            break;

        default:

            echo 0;
    }

} else {
    echo 0;
}

