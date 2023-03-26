<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function prospectusalertes_install() {
  	/*$cron = cron::byClassAndFunction('prospectusalertes', 'check');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('prospectusalertes');
		$cron->setFunction('check');
		$cron->setEnable(1);
		$cron->setSchedule('16 0,6,12,18 * * *');
		$cron->setTimeout(1440);
		$cron->save();
	}*/
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function prospectusalertes_update() {
  	/*$cron = cron::byClassAndFunction('prospectusalertes', 'check');
	if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('prospectusalertes');
		$cron->setFunction('check');
		$cron->setEnable(1);
		$cron->setSchedule('16 0,6,12,18 * * *');
		$cron->setTimeout(1440);
		$cron->save();
	}*/
}

// Fonction exécutée automatiquement après la suppression du plugin
function prospectusalertes_remove() {
	$cron = cron::byClassAndFunction('prospectusalertes', 'check');
	if (is_object($cron)) {
		$cron->remove();
	}
}