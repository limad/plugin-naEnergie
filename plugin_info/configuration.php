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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>


<form class="form-horizontal">
    <fieldset>

        <!---->
 <div class="row">       
	<div class="col-md-6 col-sm-6">
     <div class="form-group">
         <label class="col-sm-4 control-label">{{Client ID}}</label>
        <div class="col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="client_id" placeholder="Client ID"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">{{Client secret}}</label>
        <div class="col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="client_secret" placeholder="Client Secret"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">{{Nom d'utilisateur}}</label>
        <div class="col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="username" placeholder="Nom d'utilisateur"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">{{Mot de passe}}</label>
        <div class="col-sm-7">
            <input type="password" class="configKey form-control" data-l1key="password" placeholder="mot de passe"/>
        </div>
    </div>
    <!--
    <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-7">
                <input type="checkbox" class="configKey" data-l1key="getFriendsDevices" />{{Récupérer les stations amis ?}}
            </div>
    </div>
    <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-7">
                <input type="checkbox" class="configKey" data-l1key="getFavorisDevices" />{{Récupérer les stations favoris ?}}
            </div>
    </div>
    -->
      
    <div class="form-group">
        <label class="col-sm-4 control-label">{{Synchroniser}}</label>
        <div class="col-sm-7">
        <a class="btn btn-success" id="bt_syncWithStation"><i class='fas fa-refresh'></i> {{Synchroniser mes équipements}}</a>
        </div>
    </div>
    </div> <!--fin col-->
   <div class="col-md-6 col-sm-6">
	
	<div class="form-group">
            
                     
            
            <?php
	    	$hasweather = false;
	    	$hasthermostat = false;
	    	$haswelcome = false;
		try {
			$hasweather = plugin::byId('netatmoWeather');
			$hasthermostat = plugin::byId('netatmoThermostat');
			$haswelcome = plugin::byId('netatmoWelcome');
		} catch (Exception $e) {
		}
		/*if (($hasthermostat && $hasthermostat->isActive()) || ($haswelcome && $haswelcome->isActive())|| ($hasweather && $hasweather->isActive())){
			echo '<div class="form-group">
				
				<span class="label label-default" style="text-shadow : none; font-size: 16px; margin-top: 20px;">{{Equipements Netatmo: }}</span>
				</div><div class="form-group">';
				/////////////////////////read_station read_thermostat
				///<label class="col-sm-5 control-label">{{Equipements (Beta!): }}</label>
				if (($hasweather && $hasweather->isActive())){
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_station" checked=""/>{{Inclure Station méteo ?}}
						</div>';
				}
				else{
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_station" />{{Inclure Station méteo  ?}}
						</div>';
				}
				
				
				
				if (($hasthermostat && $hasthermostat->isActive())){
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_thermostat" checked=""/>{{Inclure Thermostat ?}}
						</div>';
				}
				else{
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_thermostat"/>{{Inclure Thermostat ?}}
						</div>';
				}
				
				if (($haswelcome && $haswelcome->isActive())){
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_welcome" checked=""/>{{Inclure Welcome(un jour!!) ?}}
						</div>';
				}
				else{
					echo '<div class="col-sm-7">
						<input type="checkbox" class="configKey" data-l1key="read_welcome"/>{{Inclure Welcome(un jour!!) ?}}
						</div>';
				}
				
			echo '</div>';
		}
		*/ // fin if
    ?>
            
            
            
    </div>
    
     
	</div>
</div>	 <!--row-->
	
	
	<?php
	    	$hasweather = false;
	    	$hasthermostat = false;
	    	$haswelcome = false;
		try {
			$hasweather = plugin::byId('netatmoWeather');
			$hasthermostat = plugin::byId('netatmoThermostat');
			$haswelcome = plugin::byId('netatmoWelcome');
		} catch (Exception $e) {
		}
		if (($hasthermostat && $hasthermostat->isActive()) || ($haswelcome && $haswelcome->isActive())|| ($hasweather && $hasweather->isActive())){
			echo '<div class="form-group">
			<label class="col-sm-3 control-label">{{Importer les infos depuis: }}</label>
			<div class="col-sm-2">';
			////
			if (($hasweather && $hasweather->isActive())){
				echo '<a class="btn btn-default" id="bt_getFromWeather"><i class="fas fa-random"></i> {{Netatmo Station}}</a>';
			}
			echo '</div>';
			////
			if (($haswelcome && $haswelcome->isActive())){
				echo '<a class="btn btn-default" id="bt_getFromWelcome"><i class="fas fa-random"></i> {{Netatmo Welcome}}</a>
					';
			}
			////
			if (($hasthermostat && $hasthermostat->isActive())){
				echo '<a class="btn btn-default" id="bt_getFromThermostat"><i class="fas fa-random"></i> {{Netatmo Thermostat}}</a>
					';
			}
			echo '</div>';
		}
    ?>
    
    
    
    
    

</fieldset>
</form>

<script>
	$('#bt_syncWithStation').on('click', function () {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/naEnergie/core/ajax/naEnergie.ajax.php", // url du fichier php
            data: {
                action: "syncStations",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: '{{Synchronisation réussie}}', level: 'success'});
            }
        });
    });
	/////////////////////
    $('#bt_getFromWelcome').on('click', function () {
	bootbox.confirm('{{Cela récupérera les identifiants configurés dans le plugin Netatmo Welcome, il faudra sauver avant de lancer la synchronisation. Voulez vous procéder ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/naEnergie/core/ajax/naEnergie.ajax.php", // url du fichier php
            data: {
                action: "getFromWelcome",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
				console.log(data.result[0]);
				$('.configKey[data-l1key=client_id]').empty().val(data.result[0]);
				$('.configKey[data-l1key=client_secret]').empty().val(data.result[1]);
				$('.configKey[data-l1key=username]').empty().val(data.result[2]);
				$('.configKey[data-l1key=password]').empty().val(data.result[3]);
                $('#div_alert').showAlert({message: '{{Importation réussie...Penser à sauvegarder! }}', level: 'success'});
            }
        });
    };
	});
	});
	$('#bt_getFromThermostat').on('click', function () {
	bootbox.confirm('{{Cela récupérera les identifiants configurés dans le plugin Netatmo Thermostat, il faudra sauver avant de lancer la synchronisation. Voulez vous procéder ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/naEnergie/core/ajax/naEnergie.ajax.php", // url du fichier php
            data: {
                action: "getFromThermostat",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
				console.log(data.result[0]);
				$('.configKey[data-l1key=client_id]').empty().val(data.result[0]);
				$('.configKey[data-l1key=client_secret]').empty().val(data.result[1]);
				$('.configKey[data-l1key=username]').empty().val(data.result[2]);
				$('.configKey[data-l1key=password]').empty().val(data.result[3]);
                $('#div_alert').showAlert({message: '{{Importation réussie...Penser à sauvegarder!}}', level: 'success'});
            }
        });
    };
	});
	});
	$('#bt_getFromWeather').on('click', function () {
	bootbox.confirm('{{Cela récupérera les identifiants configurés dans le plugin Netatmo Weather, il faudra sauver avant de lancer la synchronisation. Voulez vous procéder ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/naEnergie/core/ajax/naEnergie.ajax.php", // url du fichier php
            data: {
                action: "getFromWeather",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
				console.log(data.result[0]);
				$('.configKey[data-l1key=client_id]').empty().val(data.result[0]);
				$('.configKey[data-l1key=client_secret]').empty().val(data.result[1]);
				$('.configKey[data-l1key=username]').empty().val(data.result[2]);
				$('.configKey[data-l1key=password]').empty().val(data.result[3]);
                $('#div_alert').showAlert({message: '{{Importation réussie...Penser à sauvegarder!}}', level: 'success'});
            }
        });
    };
	});
	});

	


</script>
