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

Session::haveRight("config", UPDATE);

Html::header(PluginMantisConfig::getTypeName(1),
               $_SERVER['PHP_SELF'], "plugins", "mantis", "config");

if (!isset($_GET["id"])) {
   $_GET["id"] = 1;
}

$PluginMantisConfig = new PluginMantisConfig();

if (isset($_POST["update"])) {
   $PluginMantisConfig->check($_POST["id"], UPDATE);
   $PluginMantisConfig->update($_POST);
   Html::back();
}

$PluginMantisConfig->showForm($_GET["id"]);

Html::footer();