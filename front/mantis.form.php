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

}






