
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

 $('#bt_validmodif00000000000').on('click', function () {
	alert('bt_validmodif');
	$('.label-info').hide();
	$('.ndeviceid').show();
	$('.mdeviceid').show();
});
 $('#bt_modif').on('click', function () {
	alert('modif');
	$('.label-info').hide();
	$('.ndeviceid').show();
	$('.mdeviceid').show();
	
	
});

$('#bt_add').on('click', function () {
	$("#ModalAdd").modal();
	
	
	//$(' .bootbox-prompt').dialog('open');
	//$(".myModal3").show();
	//$('#md_modal').dialog({title: "{{Ajouter équipement}}"});
	//$('#md_modal').load('.bootbox-prompt').dialog('open');
});

$('#bt_addok').on('click', function () {
	alert('ok');
	
	
	//$(' .bootbox-prompt').dialog('open');
	//$(".myModal3").show();
	//$('#md_modal').dialog({title: "{{Ajouter équipement}}"});
	//$('#md_modal').load('.bootbox-prompt').dialog('open');
});

$('#bt_testnaEnergie').on('click', function () {
	//$('#md_modal').dialog({title: "{{Panel test}}"});
	//$('../../plugins/naEnergie/desktop/php/panel.php').show();
	window.open("index.php?v=d&m=naEnergie&p=panel","_self")
});


$('#bt_healthnaEnergie').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé naEnergie}}"});
    $('#md_modal').load('index.php?v=d&plugin=naEnergie&modal=health').dialog('open');
});



$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});




$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 // Fonction pour l'ajout de commande, appellé automatiquement par plugin.template

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    ////
    //var tr = '<tr class="cmdinfos" ><td colspan="5"></td></tr>';
    
    
        
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes Infos</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		//tr += '<span class="cmdAttr" data-l1key="type"></span>';
		//tr += '   /   ';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Evaluer}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    
    if (init(_cmd.type) == 'info') {
    $('#table_cmdi tbody').append(tr);
    $('#table_cmdi tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
    
    
    //var lgid = init(_cmd.logicalId);
    //init(_cmd.logicalid).substring(init(_cmd.logicalid).length-6, init(_cmd.logicalid).length)
    else if ( init(_cmd.logicalId).substring(init(_cmd.logicalId).length-6, init(_cmd.logicalId).length) !== 'mobile'){
		
    
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes mobiles</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		//tr += '<span class="cmdAttr" data-l1key="type"></span>';
		//tr += '   /   ';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmda tbody').append(tr);
    $('#table_cmda tbody tr:last').setValues(_cmd, '.cmdAttr');
		
    } else {
		
    
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes mobiles</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		//tr += '<span class="cmdAttr" data-l1key="type"></span>';
		//tr += '   /   ';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmdam tbody').append(tr);
    $('#table_cmdam tbody tr:last').setValues(_cmd, '.cmdAttr');
		
    }
    
}


$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').on('change',function(){
  	$(".eqName").empty().append($('.eqLogicAttr[data-l1key=name]').value());
    if($(this).value() != ""){
      $('#img_naEnergieModel').attr('src','plugins/naEnergie/core/img/'+$(this).value()+'.png');
    }else{
      $('#img_naEnergieModel').attr('src','plugins/naEnergie/plugin_info/naEnergie_icon.png');
      
    }
});                   


$('#bt_eqConfigRaw').off('click').on('click',function(){
  var eqid= $('.eqLogicAttr[data-l1key=id]').value();
	$('#md_modal2').dialog({title: "{{Informations brutes}}"});
	$("#md_modal2").load('index.php?v=d&modal=object.display&class=eqLogic&id='+eqid).dialog('open');
});

$('#bt_removeAll').on('click', function () {
	 console.log('init removeAll action');
	 bootbox.confirm('{{Etes-vous sûr de vouloir supprimer tous les équipements ?}}', function (result) {
	        if (result) {
	            $.ajax({
	                type: "POST", // méthode de transmission des données au fichier php
	                url: "plugins/naEnergie/core/ajax/naEnergie.ajax.php", 
	                data: {
	                    action: "removeAll",
	                    id: $('.eqLogicAttr[data-l1key=id]').value()
	                },
	                dataType: 'json',
	                global: false,
	                error: function (request, status, error) {
	                    handleAjaxError(request, status, error);
	                },
	                success: function (data) { 
	                    if (data.state != 'ok') {
	                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
	                        return;
	                    }
	                    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
	                    $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
	                }
	            });
	        }
	    });
	 console.log('end removeAll action');
 });