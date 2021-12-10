<?php

/**
 * -------------------------------------------------------------------------
 * Mantis plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Mantis.
 *
 * Mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mantis. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2014-2022 by Mantis plugin team.
 * @license   AGPLv3 https://www.gnu.org/licenses/agpl-3.0.html
 * @link      https://github.com/pluginsGLPI/mantis
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$mantis = new PluginMantisMantis();

if (isset($_GET['action']) && $_GET['action'] == 'linkToIssue') {

   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
   $mantis->getFormForLinkGlpiTicketToMantisTicket($_GET['idTicket'], $_GET['itemType']);
   Html::popFooter();
} else if (isset($_GET['action']) && $_GET['action'] == 'linkToProject') {

   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
   $mantis->getFormForLinkGlpiTicketToMantisProject($_GET['idTicket'], $_GET['itemType']);
   Html::popFooter();
} else if (isset($_GET['action']) && $_GET['action'] == 'deleteIssue') {
   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);

   $id_link = $_GET['id'];
   $id_ticket = $_GET['idTicket'];
   $id_mantis = $_GET['idMantis'];

   $mantis->getFormToDelLinkOrIssue($id_link, $id_ticket, $id_mantis, $_GET['itemType']);
   Html::popFooter();
}
