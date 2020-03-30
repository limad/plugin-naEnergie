<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugId = 'naEnergie';
$plugin = plugin::byId($plugId);
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$plugName=$plugin->getName();
?>

<style>
  #table_cmdi tr:hover{background-color: #9e9a9a;}
  #table_cmda tr:hover{background-color: #9e9a9a;}
  #table_cmdi tr:nth-child(even){background-color: #background-color#;}
</style>
<!--
*#table_cmdi tr:nth-child(even){background-color: #background-color#;}
-->

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoDefault" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
		
			<div class="cursor" id="bt_healthnaEnergie" data-action="gotoPluginConf">
				<i class="fas fa-medkit"></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
  			
  			<div class="cursor eqLogicAction logoDefault" data-action="removeAll" id="bt_removeAll">
				<i class="fas fa-minus-circle" style="color: #FA5858;"></i>
				<br/>
				<span>{{Supprimer tous}}</span>
			</div>
		</div>
		
		<legend>{{Mes thermostats}}</legend>
		
					<?php
			if (count($eqLogics) == 0) {
				echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>
                	{{Cliquez sur Configuration pour commencer !}}</span></center>";
			} 
			else {
              	echo '<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />';
				echo '<div class="eqLogicThumbnailContainer">';
				foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					
					if ($eqLogic->getIsEnable() != 1) {
						
					}
					
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
					if ($eqLogic->getConfiguration('type') != '') {
						echo '<img src="plugins/naEnergie/core/img/' . $eqLogic->getConfiguration('type', '') . '.png"/>';
					
					} else {
						echo '<img src="plugins/naEnergie/plugin_info/'.$plugId.'_icon.png" style="height : 100px"/>';
					}
					echo '<br/>';
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
					
					
				}
			}
			
	?>	
		</div>
	</div>
	
	<div class="col-xs-12 eqLogic" style="display: none;">
	<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
      			<a class="btn btn-default btn-sm roundedLeft" id="bt_eqConfigRaw"><i class="fas fa-info">  </i>  </a>
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> Configuration avancée</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> Sauvegarder</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> Supprimer</a>
			</span>
		</div>
		
		
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay">
      			<i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
      			<i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" data-toggle="tab" aria-controls="profile" role="tab" >
      			<i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		
		<!--
		<li  role="presentation"><a href="#configureAdvanced" data-toggle="tab" ><i class="fas fa-cog" aria-hidden="true"></i> {{Avancée}}</a></li>
		-->
	   <?php
			  
			?>
		</ul>
		
		
		
		
		
		
		<div class="tab-content">
			<!-- *********** eqlogictab  ****-->
			<div class="tab-pane active" id="eqlogictab">
				<br/>
				<legend><i class="fas fa-tachometer-alt"></i> {{Général}}</legend>
				<div class="row">
					<div class="col-lg-6"><!--infos-->
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-lg-4 control-label" >{{Nom de la pièce}}</label>
									<div class="col-lg-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;  " />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du thermostat}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Objet parent}}</label>
									<div class="col-lg-6">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											foreach (jeeObject::all() as $object) {
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-4 control-label">{{Activer}}</label>
									<div class="col-lg-8">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
							</fieldset>
						</form>
						<br>
						<!-- Informations  --> 
						<legend><i class="fas fa-info" aria-hidden="true"></i> {{Informations}}</legend>
						<form class="form-horizontal">
							<fieldset>
									<!--*******************************  -->
									<div class="form-group" id="ident">
										<label class="col-lg-4 control-label">{{Identifiant}}</label>
											<div class="col-lg-4">
												<input disabled  class="eqLogicAttr form-control"  data-l1key="logicalId"/>
											</div>
									</div> 
									
                                    
                                    <!--*******************************  -->
                                    <!--
                                              
                                        <div class="form-group" id="firmware" >
										<label class="col-lg-4 control-label">{{Firmware}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="firmware"/>
											</div>
											
									</div>
									-->
									<!--*******************************  -->						
									<div class="form-group" id="NAtype">
										<label class="col-lg-4 control-label">{{Type}}</label>
											<div class="col-lg-4">
												<input disabled  class="eqLogicAttr form-control"  
                                              		data-l1key="configuration" data-l2key="NAtype" />
											</div>
									</div>
                                              
                                 <!--*******************************  --> 
									<div class="form-group" id="home" >
										<label class="col-lg-4 control-label">{{Home}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="parentName"/>
											</div>
											
									</div>
                                  <!--*******************************  -->            
     <!--                                         
                                              
   <div class="form-group">
      <label class="col-lg-4 control-label">{{Type2}}</label>
      <div class="col-lg-4">
        <select type="text" disabled class="eqLogicAttr form-control natype" data-l1key="configuration" data-l2key="type" >
			
          <option value="NAMain">{{Station}}</option>
          <option value="NAModule1">{{Module extérieur}}</option>
          <option value="NAModule2">{{Anémomètre}}</option>
          <option value="NAModule3">{{Pluviomètre}}</option>
          <option value="NAModule4">{{Module intérieur}}</option>
          <option value="NAPlug">{{Relai Themostat}}</option>
          <option value="NATherm1">{{Module Themostat}}</option>
          <option value="NRV">{{Valve}}</option>
        </select>
      </div>
    </div>
       -->                                       
                                              
                                              
    
						
 
                                              
                                     
                                              
                                              
								
								
								
								
							</fieldset>
						</form>
				
				
				 
					</div>
					
					<div class="col-lg-6"><!--img-->
						<center>
									
							<span class="eqLogicAttr" data-l1key="configuration" data-l2key="type" style="display:none;"></span>
							<img id="img_naEnergieModel" src="plugins/naEnergie/plugin_info/<?php echo $plugId;?>_icon.png" style="height : 200px"/>
                                        
                                              
                   
                        </center>
					</div>
				</div>
				<!--row-->
			
					
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
			<br>	
				
			<!--Config durées par défaut-->
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fas fa-calendar" aria-hidden="true"></i> {{Configuration durées par défaut(min)}}</legend>
						<form class="form-horizontal">
						<div class="col-lg-6">
							
							
							
							<div class="form-group" id="spm_duaration">
								<label class="col-lg-4 control-label" >{{Mode Manuel}}
                                <sup><i class="fas fa-question-circle tooltips" title="Paramètre Netatmo"></i></sup>
                                 </label>
									<div class="col-lg-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="spm_duaration" placeholder="{{Durée en minutes (60 par défaut)}}"/>
									</div>
							</div> 
										<!--*******************************  --> 
							<div class="form-group" id="away_duaration" >
								<label class="col-lg-4 control-label">{{Mode absent}}
                                <sup><i class="fas fa-question-circle tooltips" title="Paramètre Jeedom"></i></sup>
                                 </label>
									<div class="col-lg-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="away_duaration" placeholder="{{Optionnel(Défaut: Nouvel Ordre)}}" />
									</div>
							</div>   
										<!--*******************************  -->  
							<div class="form-group"  id="hg_duaration">
								<label class="col-lg-4 control-label">{{Mode H.gel}}
                                	<sup><i class="fas fa-question-circle tooltips" title="Paramètre Jeedom"></i></sup>
                                </label>
									<div class="col-lg-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="hg_duaration" placeholder="{{Optionnel(Défaut: Nouvel Ordre)}}"/>
									</div>
							</div> 
						</div>
						<div class="col-lg-6"></div>
							

						
						</form>
					</fieldset>
				</form>
				
				<br>
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fas fa-wrench" aria-hidden="true"></i> {{Configuration autres...}}</legend>
						<div class="col-lg-6">
							<div class="form-group" id="areaheat">
								<label class="col-lg-4 control-label" > {{Surface chaufée (m²)}}</label>
								<div class="col-lg-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="areaheat" placeholder="{{}}" />
								</div>
							</div>
							<!--*******************************  -->
							<div class="form-group" id="tuileytpe">
								<label class="col-lg-4 control-label" > {{Type de la tuile}}</label>
								<div class="col-lg-4">
								<select id="sel_tuile" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="eqtuile">
											<option value="default" selected="selected"> {{Default}}</option>
											<!--<option value="plug2" > {{Tuile2}}</option>-->
                                            <option value="core"> {{Core}}</option>
								</select>
								
								</div>
							</div> 
							<!--*******************************  -->
                             <div class="form-group" id="temp-out">
                              <label class="col-lg-4 control-label">{{Température extérieure}}</label>
                              <div class="col-lg-8">
                                  <div class="input-group">
                                      <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_ext" data-concat="1" />
                                      <span class="input-group-btn">
                                          <a class="btn btn-default listCmdInfo"><i class="fas fa-list-alt"></i></a>
                                      </span>
                                  </div>
                              </div>
                            </div>
                            <br/>                 
                            <!--*******************************  -->   
								<!--
								<div class="form-group" id="adv_config1">
									<label class="col-lg-4 control-label"  ></label>
										<div class="col-lg-4">
											<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="advancedctrl" /><label>{{Controle avancé}} </label>
										</div>
								</div> 
								-->
						</div>
					
						<div class="col-lg-6"></div>
					</fieldset>
				</form>
			</div>
			<!-- *********** commandtab  ****-->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<legend>
					<center class="title_cmdtable">{{Tableau de commandes <?php echo ' - '.$plugName.': ';?>}}
						<span class="eqName"></span>
					</center>
				</legend>
				
				<legend><i class="fas fa-info-circle"></i>  {{Infos}}</legend>
						
						<table id="table_cmdi" class="table table-bordered table-condensed ">
							<!--<table class="table  tablesorter tablesorter-bootstrap tablesorter hasResizable table-striped hasFilters" id="table_update" style="margin-top: 5px;" role="grid"><colgroup class="tablesorter-colgroup"></colgroup>
							</table>-->
							<thead>
								<tr>
									<th style="width: 40px;">Id</th>
									<th style="width: 280px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 220px;">{{Options}}</th>
									<th style="width: 80px;">{{Action}}</th>
									 
								</tr>
							</thead>
							<tbody></tbody>
						</table>

						<legend><i class="fas fa-list-alt"></i>  {{Actions}}</legend>
						<table id="table_cmda" class="table table-bordered table-condensed">
							
							<thead>
								<tr>
									<th style="width: 40px;">Id</th>
									<th style="width: 280px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 220px;">{{Options}}</th>
									<th style="width: 80px;">{{Action}}</th>
									 
								</tr>
							</thead>
							<tbody></tbody>
						</table>

				
					</div><!--fin *********** commandtab  ****-->
                          
                          
                          
			
            <!-- *********** configureAdvanced  ****-->
			<div class="tab-pane" id="configureAdvanced">
				
					<fieldset>
						<br/>
						<div id="div_modes">
				<div class="alert alert-info">
					{{Avec les modes, vous pouvez rajouter à votre thermostat des consignes prédéfinies. Par exemple un mode confort qui déclenche une action sur votre thermostat avec une température de consigne de 20°C}}
					<a class="btn btn-success addMode pull-right" style="position: relative;top: -7px;"><i class="fas fa-plus-circle"></i> Ajouter mode</a>
				</div>
					<br/><br/>
				
				<legend><i class="fas fa-wrench"></i> {{Gestion des zones}}</legend>
				 <form class="form-horizontal">
				<fieldset>
					<legend>zone externe
						<a class="btn btn-danger btn-xs pull-right" id="bt_addTrigger" data-type="heat" style="position: relative; top : 5px;">
							<i class="fas fa-plus-circle"></i>{{ Ajouter une zone}}</a>
					</legend>
				</div>  
										
				<!--******************************* checked="0"  --> 						
										
										
				</fieldset>
			</form>
				 <div class="input-group col-lg-4">
					<select class="form-control scenarioAttr input-sm" data-l1key="mode">
						<option value="provoke" selected="selected">Consigne</option>
						<option value="schedule">Programmé</option>
					</select>
					<span class="input-group-btn">
						<a class="btn btn-default btn-sm" id="bt_addTrigger" style="">
							<i class="fas fa-plus-square"></i> Déclencheur</a>
					</span>
				</div>
				<div class="provokeMode provokeDisplay" style=""></div>						
										
					
										
										
										 <!--******************************* checked="0"  --> 
			  <div class="zext" style="">
			 
				<!--******************************* checked="0"  --> 	
					<div class="form-group" id="temp-in">
							<label class="col-lg-4 control-label">{{Température intérieure}}</label>
							<div class="col-lg-9">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_indoor" data-concat="1" />
									<span class="input-group-btn">
										<a class="btn btn-default listCmdInfo"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</div>
					</div>
					<br/><br/>
					
					<div class="form-group ">
							<label class="col-lg-1 control-label">Action</label>
							<div class="col-lg-4">
								<div class="input-group">
									<span class="input-group-btn">
										<a class="btn btn-default bt_removeAction btn-sm" data-type="heat">
											<i class="fas fa-minus-circle"></i>
										</a>
									</span>
									<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="heat">
									<span class="input-group-btn">
										<a class="btn btn-default btn-sm listCmdAction" data-type="heat"><i class="fas fa-list-alt"></i></a>
									</span>
								</div>
							</div>
							<div class="col-lg-7 actionOptions"></div>
						</div>
					
					<br/>
					
					
					
					<div class="activationOk"><hr>
						<div class="form-group ">
							<div class="col-lg-1 form-group" style="margin-left:10px; width:220px">
								<input type="hidden" name="id_equip" id="id_equip" value="$id" >
								<a class="btn btn-default bt_removeAction btn-sm" data-type="activationOk" style="margin-right: 20px" title="Supprimer la ligne d'action">
									<i class="fas fa-trash"></i>
								</a> &nbsp;activée 
								<input style="margin-right: 20px"  class="actionactiveattr" type="checkbox" data-l1key="actionactive" value="oui" title="Activer ou désactiver cette action" checked="">
								<label class="control-label">Action :</label>
							</div>
						<div class="col-lg-4 has-success">
						<div class="input-group">
							<span class="input-group-btn">
							</span><input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="activationOk">
							<span class="input-group-btn">
								<a class="btn btn-success btn-sm listCmdAction" data-type="activationOk" title="Sélectionner une action sur un équipement ou plugin"><i class="fas fa-cube"></i></a><a class="btn btn-sm btn-default AddPause" data-type="activationOk" title="Faire une pause"><i class="fas fa-hourglass-start"></i></a><a class="btn btn-sm btn-default AddScenario" data-type="activationOk" title="Agir sur un scénario"><i class="fas fa-tasks"></i></a><a class="btn btn-sm btn-default AddMessage" data-type="activationOk" title="Ajouter un message au centre de messages de Jeedom"><i class="fas fa-envelope-o"></i></a></span></div></div><div class="col-lg-1 form-group" style="margin-left:10px; width:160px"><label class="control-label">A condition que :</label></div><div class="col-lg-5 alert alert-info" style="max-width:450px">Les conditions seront ajoutées dans une prochaine version du plugin</div></div><label class="col-lg-1 control-label"></label><div class="col-lg-10 actionOptions"></div><br><br><br><br><br></div>
					<br/>
					
			</div>
			
					</fieldset>
				</form>
			<!-- *********** configurePlanning  ****-->
			
			<div class="tab-pane" id="configurePlanning">
				<form class="form-horizontal">
					<fieldset>
						<br/><br/>
						
						
					</fieldset>
				</form>
			</div>
				
		</div>
		<!-- *********** div class="tab-content" ****-->
	</div>
</div>
<?php include_file('desktop', 'naEnergie', 'js', 'naEnergie');?>
<?php include_file('core', 'plugin.template', 'js');?>