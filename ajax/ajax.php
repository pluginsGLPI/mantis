<?php

include('../../../inc/includes.php');
require_once('../inc/mantisWS.class.php');
require_once('../inc/mantis.class.php');
require_once('../inc/mantisIssue.class.php');


if (isset($_POST['action'])) {

   switch ($_POST['action']) {

      case 'testConnexionMantisWS':
         $ws = new PluginMantisMantisws();
         echo $ws->testConnectionWS($_POST['host'], $_POST['url'], $_POST['login'],$_POST['pwd']);
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

         $id_ticket       = $_POST['idTicket'];
         $id_mantis_issue = $_POST['idMantis'];
         $ws              = new PluginMantisMantisws();
         $ws->initializeConnection();

         //on verifie que l'id du ticket mantis existe
         if (!$ws->existIssueWithId($id_mantis_issue)) {
            echo __("MAntisBT issue does not exist","mantis");
         } else {
            $mantis = new PluginMantisMantis();
            //on verifie si un lien est deja creé
            if ($mantis->IfExistLink($id_ticket, $id_mantis_issue)) {
               echo __("This Glpi ticket is already linked to this MantisBT ticket","mantis");
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

      case 'deleteLinkMantis':

         $mantis = new PluginMantisMantis();
         $ws     = new PluginMantisMantisws();
         $ws->initializeConnection();

         $res = $mantis->delete($_POST);

         if($res)echo true;
         else echo __("Error while deleting the link between Glpi ticket and MantisBT ticket", "mantis");


         break;

      case 'deleteIssueMantisAndLink':

         $mantis = new PluginMantisMantis();
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();


         if ($ws->existIssueWithId($_POST['idMantis'])) {

            if ($del = $ws->deleteIssue($_POST['idMantis'])) {

               $res = $mantis->delete($_POST);
               if($res)echo true;
               else echo __("Error while deleting the link between Glpi ticket and MantisBT ticket", "mantis");

            } else {
            echo __("Error while deleting the mantisBD ticket", "mantis");
            }

         } else {
            echo __("The MantisBT ticket does not exist", "mantis");
         }

         break;

      default:

         echo 0;
   }

} else {
   echo 0;
}

