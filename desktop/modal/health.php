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
/*
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
* */

//$eqLogics = nagraphs::byType('nagraphs');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('naEnergie');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<table class="table table-condensed tablesorter" id="table_healthnaEnergie">
	<thead>
		<tr>
			<th>{{Module}}</th>
			<th>{{ID}}</th>
			<th>{{Device}}</th>
			<th>{{Wifi}}</th>
			<th>{{Rf}}</th>
			<th>{{Batterie}}</th>
			<th>{{Statut}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
	
foreach ($eqLogics as $eqLogic) {
	echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getLogicalId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getStatus('wifistatus') . ' </span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getStatus('rfstatus') . '</span></td>';
	
	$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
	$battery = $eqLogic->getStatus('battery');//$eqLogic->getConfiguration('battery_percent');
	if ($battery == '') {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fas fa-plug"></i></span>';
  } elseif ($battery < 20) {
		$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery < 60) {
		$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery > 60) {
		$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
	} else {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;">' . $battery . '%</span>';
	}
	echo '<td>' . $battery_status . '</td>';
	
	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	$lastComm=$eqLogic->getStatus('lastCommunication');
	if ((time()-strtotime($lastComm)) > 60*60 ){
		echo '<td><span class="label label-danger" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	
	}elseif ((time()-strtotime($lastComm)) > 30*60 ){
		echo '<td><span class="label label-warning" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	
	}else{
		echo '<td><span class="label label-success" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	}
	//echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}

?>
	</tbody>
</table>