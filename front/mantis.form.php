<?php
include('../../../inc/includes.php');
require_once('../inc/mantis.class.php');

$mantis = new PluginMantisMantis();

if (isset($_GET['action']) && $_GET['action'] == 'linkToIssue') {

    Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
    $mantis->getFormForLinkGlpiTicketToMantisTicket($_GET['idTicket']);
    Html::popFooter();

} else if (isset($_GET['action']) && $_GET['action'] == 'linkToProject') {

    Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
    $mantis->getFormForLinkGlpiTicketToMantisProject($_GET['idTicket']);
    Html::popFooter();

}else if (isset($_GET['action']) && $_GET['action'] == 'deleteIssue'){
   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);

   $id_link = $_GET['id'];
   $id_ticket = $_GET['idTicket'];
   $id_mantis = $_GET['idMantis'];

   $mantis->getFormToDelLinkOrIssue($id_link, $id_ticket, $id_mantis);
   Html::popFooter();
}






