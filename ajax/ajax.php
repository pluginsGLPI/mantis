<?php

/*
   ------------------------------------------------------------------------
   GLPI Plugin MantisBT
   Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.

   https://forge.indepnet.net/projects/mantis
   ------------------------------------------------------------------------

   LICENSE

   This file is part of GLPI Plugin MantisBT project.

   GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   GLPI Plugin MantisBT is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   GLPI Plugin MantisBT
   @author    Stanislas Kita (teclib')
   @co-author François Legastelois (teclib')
   @co-author Le Conseil d'Etat
   @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
   @license   GPLv3 or (at your option) any later version
              http://www.gnu.org/licenses/gpl.html
   @link      https://forge.indepnet.net/projects/mantis
   @since     2014

   ------------------------------------------------------------------------
 */
   
include('../../../inc/includes.php');

if (isset($_POST['action'])) {

    global $CFG_GLPI;

   switch ($_POST['action']) {


      case 'testConnexionMantisWS':
         error_reporting(0);
         $ws = new PluginMantisMantisws();
         try {
            $res = $ws->testConnectionWS($_POST['host'], $_POST['url'], $_POST['login'], $_POST['pwd']);
            if ($res) {
               echo "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/check24.png'/>";
            } else {
               echo "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/cross24.png'/>Access denied";
            }
         } catch (Exception $e) {
            echo "<img src='" . $CFG_GLPI['root_doc'] . "/plugins/mantis/pics/cross24.png'/>Error IP or Path";
         }

         break;

      case 'findIssueById':
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();
         $res =  $ws->existIssueWithId($_POST['id']);
         if($res) echo "<img src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/check24.png' />";
         else echo "<img src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/cross24.png'/>";

         break;

      case 'findProjectByName':
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();
         $res =  $ws->existProjectWithName($_POST['name']);
         if($res) echo "<img id='resultImg' src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/check24.png' />";
         else echo "<img id='resultImg' src='".$CFG_GLPI['root_doc']."/plugins/mantis/pics/cross24.png'/>";
         break;


       case 'getStateMantis':

           $ws = new PluginMantisMantisws();
           $ws->getConnexion($_POST['host'], $_POST['url'], $_POST['login'], $_POST['pwd']);
           $result = $ws->getStateMantis();




           if (!$result) echo false;
           else {
                $states = "";
               $i = 0;
               foreach ($result as &$state) {
                   if($i == 0) $states .= $state->name;
                   else $states .= ",".$state->name;
                   $i++;
               }

               echo $states;
           }



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
            echo __("MantisBT issue does not exist","mantis");
         } else {
            $mantis = new PluginMantisMantis();
            //on verifie si un lien est deja creé
            if ($mantis->IfExistLink($id_ticket, $id_mantis_issue)) {
               echo __("This Glpi ticket is already linked to this MantisBT ticket","mantis");
            } else {

               $issue = new PluginMantisIssue();
               $res = $issue->addInfoToIssueMantis($id_ticket,$id_mantis_issue);

                if($res){
                    $mantis->add($_POST);
                    echo true;
                }else{
                    echo $res;
                }


            }
         }
         break;


      case 'LinkIssueGlpiToProjectMantis':
         $issue = new PluginMantisIssue();
         echo $issue->linkisuetoProjectMantis();
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

