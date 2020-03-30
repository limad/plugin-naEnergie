<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
//if (!class_exists('\..\..\..\naEnergie\3rparty\Netatmo-API-PHP\autoload')) {
require_once dirname(__FILE__) . '/../../../naEnergie/3rparty/Netatmo-API-PHP/autoload.php';
//}

/* * ***************************class naEnergie********************************* */
class naEnergie extends eqLogic {
	
	//public static $_widgetPossibility = array('custom' => true);
  	public static $_widgetPossibility = array('custom' => 'true');//'parameters['allow_displayType',default ],'advanceWidgetParameter'
  	private static $_templateArray = array();
	public static $_data = array();
  	public static $_global;
	public static $_conf = array();
	private static $_client = null;
	private static $_clientTh = null;
	private static $_nascope = null;
	public static $_homeIds = array();
	
////////*****************************************////////
	public function getConf($param, $default = NULL){
		//if(naEnergie::$_conf[$param]){
	//		return naEnergie::$_conf[$param];
		//}
		return isset($this->_conf[$name]) ? $this->conf[$name] : $default;
	}
////////*****************************************////////	
	public function setConf($name=null,$value=null){
		if(isset($name) && !is_array($name) ){
			naEnergie::$_conf[$name]=$value;	
		//return ;
		}else{
			//naEnergie::$_conf[]=$name;
			array_merge($name,smartDlink::$_conf);	
		}
		
	}
////////*****************************************////////
	public static function getFromWelcome() {
		$client_id = config::byKey('client_id', 'netatmoWelcome');
		$client_secret = config::byKey('client_secret', 'netatmoWelcome');
		$username = config::byKey('username', 'netatmoWelcome');
		$password = config::byKey('password', 'netatmoWelcome');
		return (array($client_id,$client_secret,$username,$password));
	}
///////////////////////////////	
	public static function getFromThermostat() {
		$client_id = config::byKey('client_id', 'netatmoThermostat');
		$client_secret = config::byKey('client_secret', 'netatmoThermostat');
		$username = config::byKey('username', 'netatmoThermostat');
		$password = config::byKey('password', 'netatmoThermostat');
		return (array($client_id,$client_secret,$username,$password));
	}
///////////////////////////////
	public static function getFromWeather() {
		$client_id = config::byKey('client_id', 'netatmoWeather');
		$client_secret = config::byKey('client_secret', 'netatmoWeather');
		$username = config::byKey('username', 'netatmoWeather');
		$password = config::byKey('password', 'netatmoWeather');
		return (array($client_id,$client_secret,$username,$password));
	}
///////////////////////////////
	public static function getFromOther($other) {
		$client_id = config::byKey('client_id', $other);
		$client_secret = config::byKey('client_secret', $other);
		$username = config::byKey('username', $other);
		$password = config::byKey('password', $other);
		return (array($client_id,$client_secret,$username,$password));
	}
///////////////////////////////

	public static function getClient($int=NULL,$gateway=NULL) {
		
		
		if (self::$_client === null){
			self::$_client = new Netatmo\Clients\NAThermApiClient(
								array(
								'client_id' => config::byKey('client_id', 'naEnergie'),
								'client_secret' => config::byKey('client_secret', 'naEnergie'),
								'username' => config::byKey('username', 'naEnergie'),
								'password' => config::byKey('password', 'naEnergie'),
								'scope' => 'read_thermostat write_thermostat'
								));
		}
		
		/*if($sc_station!=""){
				self::$_client = new Netatmo\Clients\NAWSApiClient($config);
				log::add('naEnergie', 'debug', 'sc_station: client');
			}				
			if($sc_thermostat!=""){
				
				//self::$_clientTh = new  Netatmo\Clients\NAThermApiClient($config);
				self::$_clientTh = new Netatmo\Clients\NAThermApiClient($config);
				log::add('naEnergie', 'debug', 'sc_thermostat : clientTh');
			}
			* */						
		
		try
		{
			$token = self::$_client->getAccessToken();
			
		}
		catch(Netatmo\Exceptions\NAClientException $ex)
		{
			log::add('naEnergie', 'error', "Erreur lors de la r\écup\ération du tokens: " .$ex->getMessage()."");
		}	
		
			//Homedata
		naEnergie::$_data = self::$_client->_getHomesdata();
			
		//naEnergie::$_data=$dataTh;
		//naEnergie::$_data=array('Ws' => $dataWs, 'Th' => $dataTh);
		
		if($int===NULL){
			log::add('naEnergie', 'debug', 'tokens: '.$tokens['access_token']);
			log::add('naEnergie', 'debug', 'homeData: '.json_encode(naEnergie::$_data));
			
		}
		
		
		
		return self::$_client;
	}
	
	/***** setroomthermmode: Set the home heating system to use schedule/ away/ frost guard mode****/
	public function changeHomeTherm($multiId,$action,$endtime = NULL) {
		$ids = explode('|', $multiId);
		$homeid= $ids[1];
		
		$client = self::getClient(__FUNCTION__);
		//_setThermMode($home_id, $mode,$endtime = NULL)
		
		switch ($action) {
			case 'schedule':
				$apicmd=$client->_setThermMode($homeid, $action);
				break;
          	case 'away':
            	$confLength=$this->getConfiguration('away_duaration',null);
            	if(!$endtime && $confLength){
                  	$endtime = time() + ($confLength* 60);
                }
				$apicmd=$client->_setThermMode($homeid, $action, $endtime);
				break;
			case 'hg':
            	$confLength=$this->getConfiguration('hg_duaration',null);
            	if(!$endtime && $confLength){
                  	$endtime = time() + ($confLength* 60);
                }
            	$apicmd=$client->_setThermMode($homeid, 'hg', $endtime);
				break;
		}
		sleep(3);
		if($apicmd['status']!='ok'){
			log::add('naEnergie','debug',''.__FUNCTION__ .' action: set-'.$action. ' non éxécutée: '. $apicmd['status']);
		} else{
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' action: set-'.$action.' Result: '.json_encode($apicmd['status']));
			naEnergie::getDataRoom($multiId);
		}	
		
    }
	
//////////////////////////////////////changeThermPoint($multiId,'manual', $endtime, $consOrder)
	public function changeThermPoint($multiId,$action,$endtime=null,$setpoint=null) {
		//setmode:home,manual,off
		$ids = explode('|', $multiId);
		$roomid= $ids[0];
		$homeid= $ids[1];
		log::add('naEnergie', 'debug', ''.__FUNCTION__ .' action: '.$action.'|'.$setpoint.'|'.$endtime.'| sur homeid: '.$homeid);	
		if ($endtime==null) {
			$length = $this->getConfiguration('spm_duaration');
			if ($length == null || $length == '') {
				$length = 60;
			}
			$length = time() + ($length* 60);
		} else {
			$length = ($endtime-time())/60;
		}
		
		$client = self::getClient(__FUNCTION__);
		//$client->setToManualMode($home_id, $modid, $setpoint, $endtime);
		
		if($action==="cancelOff" || $action==="home"){
          	log::add('naEnergie', 'debug', ''.__FUNCTION__ .'Setting room to : '.$action);
			$apicmd=$client->_setThermPoint($homeid, $roomid, 'home');
		} elseif($action==="manual"){
			log::add('naEnergie', 'debug',''.__FUNCTION__ ." Setting temperature to : " . $setpoint . " for " . $length . " minutes");
			$apicmd=$client->_setThermPoint($homeid, $roomid, 'manual', $length, $setpoint );
          //home_id,room_id,mode,endtime,temp
		}elseif($action==="off"){
			log::add('naEnergie', 'debug',''.__FUNCTION__ ." Setting to off: ");
			$apicmd=$client->_setThermPoint($homeid, $roomid, 'off');
		}elseif($action==="homeboost"){
			
        	foreach(eqLogic::byLogicalId($multiId, 'naEnergie') as $eqLogic) {
				if ($eqLogic->getIsEnable() != 1) {
                 	break;
                }
				list($eq_roomid,$eq_homeid) = explode('|', $eqLogic->getLogicalId());
				if ($eq_homeid == $homeid) {
                 	$apicmd=$client->_setThermPoint($eq_homeid, $eq_roomid, 'manual', $length, $setpoint);
					log::add('naEnergie', 'debug',''.__FUNCTION__ ." Setting: ".$this->getName().' to: '.$setpoint);
                }	
				
            }
        
        }
		
		
		sleep(3);
		if($apicmd['status'] != 'ok'){
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' '.$action.' non éxécutée: '.$apicmd['status']);
		} else{
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' action: '.$action.' Result: '.json_encode($apicmd['status']));
			naEnergie::getDataRoom($multiId);
		}
    }
	
//////////////////////////////////////
	public function changeRoomTherm($multiId,$action,$setpoint=null,$endtime=null) {
		//setmode:home,manual,off
		$ids = explode('|', $multiId);
		$roomid= $ids[0];
		$homeid= $ids[1];
		log::add('naEnergie', 'debug', ''.__FUNCTION__ .' action: '.$action.'|'.$setpoint.'|'.$endtime.'| sur homeid: '.$homeid);	
		if ($endtime==null) {
			$length = $this->getConfiguration('spm_duaration');
			if ($length == null || $length == '') {
				$length = 60;
			}
			$length = time() + ($length* 60);
		} else {
			$length = ($endtime-time())/60;
		}
		
		$client = self::getClient(__FUNCTION__);
		//$client->setToManualMode($home_id, $modid, $setpoint, $endtime);
		
		if($action==="roomAuto" || $action==="schedule"){
			$setmode="home";
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .'Setting room to : '.$action);
			$apicmd=$client->_setRoomThermPoint($homeid, $roomid, 'home');
		} elseif($action==="manual"){
			$setmode="manual";
			log::add('naEnergie', 'debug',''.__FUNCTION__ ." Setting temperature to : " . $setpoint . " for " . $length . " minutes");
			$apicmd=$client->_setRoomThermPoint($homeid, $roomid, $setmode, $setpoint, $length);
		} elseif($action==="off"){
			$setmode="off";
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .'Setting room to : '.$action);
			$apicmd=$client->_setRoomThermPoint($homeid, $roomid, $setmode);
		} elseif($action==='max'){
			$setmode='max';
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .'Setting room to : '.$action);
			$apicmd=$client->_setRoomThermPoint($homeid, $roomid, $setmode);
		}
		
		sleep(3);
		if($apicmd['status'] != 'ok'){
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' '.$action.' non éxécutée: '.$apicmd['status']);
		} else{
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' action: '.$action.' Result: '.json_encode($apicmd['status']));
			naEnergie::getDataRoom($multiId);
		}
    }

//////////////////////////////////////
	public function getPlanningId($planName) {
        $planning_list =  $this->getCmd(null, 'listplanning')->execCmd();
        $planning_el = explode( '|' , $planning_list);
        foreach ($planning_el as $planning) {
			list($plan_id, $plan_name) = explode( ';' , $planning);
			if ($planName == $plan_name) {
				log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Planning found : '.$planName.' to: '.$detail_planning[1]);
				$scheduleid = $plan_id;
			} 
		}
    }

//////////////////////////////////////
	public function changescheduleTherm($multiId, $scheduleid) {
		
		$ids = explode('|', $multiId);
		$homeid= $ids[1];
		$client = self::getClient(__FUNCTION__);
      	//log::add('naEnergie', 'debug', ''.__FUNCTION__ .' $scheduleid : '.substr($scheduleid, 0, 1).strlen($scheduleid));
		
      	if(substr($scheduleid, 0, 1).strlen($scheduleid) === 524){
			$set_scheduleid=$scheduleid;
		}else {
			$planning_string =  $this->getCmd(null, 'listplanning')->execCmd();
			$planning_list = explode( ';' , $planning_string);
			//log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Plannings : '.json_ecode($planning_list));
			foreach ($planning_list as $planning) {
				//$detail_planning = 
				list($planningId, $planningName) = explode( '|' , $planning);
              	if ($scheduleid == $planningName) {
					log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Planning found : '.$scheduleid.' to: '.$planningId);
					$set_scheduleid = $planningId;
				} else {
					$set_scheduleid = $scheduleid;
                  	log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Planning inconnue : '.$scheduleid);
				}
			}
		}
		
		
		$apicmd=$client->_switchSchedule($homeid, $set_scheduleid);
		sleep(3);
		
		if($apicmd['status'] != 'ok'){
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' non éxécutée: '.$apicmd['status']);
		} else{
			log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Planning set to: '.$scheduleid.' '.json_encode($apicmd['status']));
		
			naEnergie::getDataRoom($multiId);
		}
    }

////////////////////////////////	
	public function infoStation($update=false) {
		
		log::add('naEnergie', 'debug', '******** Starting '.__FUNCTION__ .'********');
		$client = self::getClient();
		$data = naEnergie::$_data;
		foreach($data['homes'] as $home) {//array multi scope
			$homeid=$home['id'];
			$homeName=$home['name'];
			$eqlist=[];
          	log::add('naEnergie', 'debug', '  ******'.__FUNCTION__ .' homeid: '.$homeName.'('.$homeid.') ******');
			naEnergie::$_homeIds[]=$home; 
			
          	foreach($home['modules'] as $module) {//foreach3
				$type=null;
                //if (in_array($module['id'], $room['module_ids'] )){
                if($module['type'] === "NAPlug"){
                    $plug_id=$module['id'];
                    $plug_name=$module['name'];
                    $plug_modules=$module['modules_bridged'];
                    break;
                }
            }
          	$eqHome = eqLogic::byLogicalId($homeid, 'naEnergie');
			if (!is_object($eqHome) || $eqHome->getLogicalId() != $homeid ) {// 
						$eqHome = new naEnergie();
						$eqHome->setIsEnable(1);
						$eqHome->setIsVisible(1);
						
						$eqHome->setEqType_name('naEnergie');
						$eqHome->setName($homeName.'-'.$plug_name);
						$eqHome->setLogicalId($homeid);
						$eqHome->setConfiguration('HomeId', $home['id']);
              			$eqHome->setConfiguration('plug_id', $plug_id);
              			$eqHome->setConfiguration('modules_bridged', $plug_modules);
						$eqHome->setCategory('heating', 1);
						$eqHome->setConfiguration('UpNumberFailed', 0);
                      	$eqHome->setConfiguration('type', 'home');//$eqHome->setConfiguration('roomName', $roomName);
                      	$eqHome->setConfiguration('parentName', $homeName);
						$eqHome->setConfiguration('roomType', 'Home');
						//$eqHome->setConfiguration('modules', $modules);
                      	$eqHome->setConfiguration('eqtuile', 'default');
						$eqHome->setConfiguration('spm_duaration',$home['therm_setpoint_default_duration']);
                  		$eqHome->save();
                }
                  
                  //log::add('naEnergie', 'debug', ''.__FUNCTION__ .'homeIds '.json_encode(naEnergie::$_homeIds));	
			
			foreach($home['rooms'] as $room){//foreach3
					$roomId=$room['id'];
					$roomType=$room['type'];
					$roomName=$room['name'];
					$modules=$room['module_ids'];
					$nbModules=count($room['module_ids']);
				
					if($room['id'] != ''){
						$eqLog_id=$room['id'].'|'.$home['id'];
						log::add('naEnergie', 'debug', '    '.__FUNCTION__.' Equipement found...: '.$roomName.' - '.$roomType.' - '.' multi_id: '.$eqLog_id.'Modules: '.json_encode($room['module_ids']));
					
						$eqLogic = eqLogic::byLogicalId($eqLog_id, 'naEnergie');
					}
					
					if (!is_object($eqLogic)) {//$update=true 
						$eqLogic = new naEnergie();
						$eqLogic->setIsEnable(0);
						$eqLogic->setIsVisible(1);
						//if ($update=true) {//$update=true 
						$eqLogic->setEqType_name('naEnergie');
						$eqLogic->setName($room['name']);
						$eqLogic->setLogicalId($eqLog_id);
						$eqLogic->setConfiguration('HomeId', $home['id']);
						$eqLogic->setCategory('heating', 1);
						$eqLogic->setConfiguration('UpNumberFailed', 0);
                      	$eqLogic->setConfiguration('roomName', $roomName);
                      	$eqLogic->setConfiguration('parentName', $homeName);
						$eqLogic->setConfiguration('roomType', self::getRoomType($room['type']));
						$eqLogic->setConfiguration('modules', $modules);
                      	$eqLogic->setConfiguration('eqtuile', 'default');
						$eqLogic->setConfiguration('spm_duaration',$home['therm_setpoint_default_duration']);
						foreach($home['modules'] as $module) {//foreach3
							$type=null;
                            //if (in_array($module['id'], $room['module_ids'] )){
                            if($module['id'] === $room['module_ids'][0]){
                              	if($module['type'] == 'NATherm1'){
                              		$type=$module['type'];
                                  	$eqLogic->setConfiguration('type', $module['type']);
                                  	$eqMaster=TRUE;
                                  	$eqLogic->setConfiguration('eqMaster', $eqMaster);
                                  	//config::save('naEqMaster', $eqLog_id, __CLASS__);
                                  	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .' eqMaster set: '.$roomName.'='.$type);
                              	}elseif(!$eqMaster && $room['type']=='livingroom'){
                              		$eqMaster = TRUE;
                                  	$eqLogic->setConfiguration('eqMaster', $eqMaster);
                                  	//config::save('naEqMaster_', $eqLog_id, 'naEnergie');
                                  	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .' eqMaster set: '.$roomName.'='.$module['type']);
                              	}//log::add('naEnergie', 'debug', 'st module_ids...: '.$room['module_ids'][0]);
								if(null == $type )$eqLogic->setConfiguration('type', $module['type']);/////////////
                              	$eqLogic->setConfiguration('NAtype', self::getNaType($module['type']));
								if(count($room['module_ids']) > 1){
                                  	$eqLogic->setConfiguration('isMulti', true);
                                }else{
                                  	$eqLogic->setConfiguration('isMulti', false);
                                  	$battery= ($type=='NATherm1' ? '3x1.5V AAA/LR03' : '2x1.5V two AA/LR06');
                                  	$eqLogic->setConfiguration('battery_type', $battery);
                                }
                                $eqLogic->setConfiguration('croncount', 0);
							}
						}
						$eqLogic->save();
                      	
						log::add('naEnergie', 'debug', '    '.__FUNCTION__.' Equipement added...: '.$room['name'].' - '.$room['type'].' - '.$eqLog_id);
					}else{
						log::add('naEnergie', 'debug', '    '.__FUNCTION__.' Allready exist! ...: '.$room['name'].' - '.$room['type'].' - '.$eqLog_id);
					}
					$eqlist[]=eqLogic::byLogicalId($eqLog_id, __CLASS__);
			}
			if(!$eqMaster){
            	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .' No eqMaster set ');
            	foreach($eqlist as $key=>$eqLogic) {
                  	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .' eq in home: '.$key.'--'.$eqLogic->getName());
                  	if($eqLogic->getConfiguration('eqMaster', false) != true){
                      	$eqMaster=false;
                      //continue;
                    } else{
                    	break;
                    }
                }
              	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .' eqMaster to set: '.$eqlist[0]->getName());
             }
        
        
        }
		//fin foreach($data['homes'] as $home)
		self::getDataRoom();
	}
///////////////////////////////////////
	public static function getDataRoom($multiId = null, $cronPhase=null){
		$client=naEnergie::getClient(__FUNCTION__);
		
		if($multiId !== null){
			$eqLogics[] = eqLogic::byLogicalId($multiId, 'naEnergie');
		}else{//(empty($eqLogics) || $eqLogics==null || $eqLogics=='')
			$eqLogics = eqLogic::byType('naEnergie', true);
		}//fin else	
			
            
      	$global = [];
		$darray = array();		
		$apiHome=array();
		$eqCount=0;
		foreach($eqLogics as $eqLogic) {
			if (!$eqLogic->getIsEnable() || $eqLogic->getConfiguration('type')=='home') {
              	//log::add('naEnergie','debug', '	'.__FUNCTION__ . '****  break '.$eqLogic->getIsEnable());
              	continue;
            }
			log::add('naEnergie','debug','***********************  eqlogiq '.$eqCount.' '.$eqLogic->getName().' ***********************');
		
			$eqLog_id=$eqLogic->getLogicalId();
			list($roomid, $homeid) = explode('|', $eqLog_id);
				  
			$confModules=$eqLogic->getConfiguration('modules');
					
			$changed = false;
			
          	if( empty($apiHome) || empty($apiHome[$homeid]) ){
				$apiHome[$homeid]['Data']=$client->_getHomesdata($homeid)['homes'][0];//attention 0
				$apiHome[$homeid]['Status']=$client->_getHomestatus($homeid)['home'];
				$global[$homeid]['SyncTime']=date('d/m/Y H:i');
				log::add('naEnergie', 'debug', ''.__FUNCTION__ .' rqst apiHome '.$eqCount.': '.json_encode($apiHome));
			}
			$global[$homeid][$eqLog_id]=[];
          	$global[$homeid]['home_name']=$apiHome[$homeid]['Data']['name'];
            $global[$homeid]['spm_duaration']=$sp_duration=$apiHome[$homeid]['Data']['therm_setpoint_default_duration'];
				//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' sp_duration: '.$sp_duration);
          	$global[$homeid]['home_modetech']=$home_modetech=$apiHome[$homeid]['Data']['therm_mode'];
				log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' home_modetech: '.$home_modetech);
          	
          
          	$datamodules=$apiHome[$homeid]['Data']['modules'];
			foreach ($datamodules as $keydm=>$datamodule) {
				$data_moduleid=$datamodule['id'];
              	if (in_array($data_moduleid, $confModules )){
					$globalmodules[$eqLog_id][$keydm]=$datamodule;
                  
                  	//$key = array_search($data_moduleid, $apiHome[$homeid]['Status']['modules']); 
                 	$key_search = array_search($data_moduleid, array_column($apiHome[$homeid]['Status']['modules'], 'id'));
                  	//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' global module name: '.$datamodules[$keydm]['name']);
				  	//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' key: '.$key.' name: '.$apiHome[$homeid]['Status']['modules'][$key_search]['type']);
					$global[$homeid][$eqLog_id]['modules'][]=
                  	array_merge($datamodule, $apiHome[$homeid]['Status']['modules'][$key_search] );
                
                	
                }
			}
          //fin foreach ($datamodules as $keydm=>$datamodule
          
          	////			
			//$statusmodules=$apiHome[$homeid]['Status']['modules'];
          	$statusmodules=$global[$homeid][$eqLog_id]['modules'];
			foreach ($statusmodules as $keyhm=>$statmodule) {
				$stat_moduleid=$statmodule['id'];
              	if (!isset($global[$homeid]['wifi_strength']) &&  $statmodule['type']=='NAPlug'){
                	$wifi_strength=$global[$homeid]['wifi_strength'] = $statmodule['wifi_strength'] ;
                  	log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' wifi_strength: '.$global[$homeid]['wifi_strength']);
                }
              	
                if (in_array($stat_moduleid, $confModules)){ 
					//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' eqModuleId found !: '.$keyhm.' | '.$stat_moduleid);
					
                  
                  	if(!isset($global[$homeid]['boiler_status']) ){//&& isset($statmodule['boiler_status'])//$statmodule['type']='NATherm1'
                         if( isset($statmodule['boiler_status']) ){
                            $global[$homeid]['boiler_status']=$boiler_status=($statmodule['boiler_status'] != false) ? 1: 0;
                         }
                    }
                    
                  	if(!isset($global[$homeid]['boiler_anticipating']) ){//&& isset($statmodule['anticipating'])$statmodule['type']='NATherm1'
						if( isset($statmodule['anticipating']) ){
                        	$global[$homeid]['boiler_anticipating']=$boiler_anticipating = ($statmodule['anticipating'] != false) ? 1: 0;
                        }
                    }else{
                     	$boiler_anticipating = $global[$homeid]['boiler_anticipating'];
                    } 
                  
                  	if(!isset($global[$homeid]['therm_comfortboost']) && isset($statmodule['anticipating'])){//$statmodule['type']='NATherm1'
						$global[$homeid]['therm_comfortboost']=$therm_comfortboost = ($statmodule['boiler_valve_comfort_boost'] != false) ? 1: 0;
					}
                  
                  	
					
					
				}
			}
          	//fin foreach ($statusmodules as $keyhm=>$module
          	$getplanning = $eqLogic->getCalendar($apiHome[$homeid]['Data']['therm_schedules'], $roomid);
          	log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' getplanning: '.json_encode($getplanning));  
			
          
          	if(!isset($global[$homeid]['listplanning'])  || $global[$homeid]['listplanning']==null){
				$global[$homeid]['listplanning'] = $listplanning = $getplanning['planings']['listplanning'];
				$global[$homeid]['nowplanning'] = $nowplanning = $getplanning['planings']['nowplanning'];
				$global[$homeid]['nowplanid'] = $nowplanid = $getplanning['planings']['nowplanid'];
				$global[$homeid]['away_temp'] = $away_temp = $getplanning['planings']['away_temp'];	
				$global[$homeid]['hg_temp'] = $hg_temp = $getplanning['planings']['hg_temp'];	
				$global[$homeid]['plan_type'] = $plan_type = $getplanning['planings']['plan_type'];
			}
			  
			//Profiles
			$global[$homeid][$eqLog_id]['listprofil']=$listprofil=$getplanning['profils']['listprofil'];
			$global[$homeid][$eqLog_id]['nowprofil_temp'] = $nowprofil_temp = $getplanning['profils']['nowprofil_temp'];
			$global[$homeid][$eqLog_id]['nowprofil'] = $nowprofil = $getplanning['profils']['nowprofil'];
			$global[$homeid][$eqLog_id]['nextprofil_temp'] = $nextprofil_temp = $getplanning['profils']['nextprofil_temp'];
			$global[$homeid][$eqLog_id]['nextprofil'] = $nextprofil = $getplanning['profils']['nextprofil'].'-'.$nextprofil_temp;
			$global[$homeid][$eqLog_id]['profil_endtime'] = $profil_endtime = $getplanning['profils']['profil_endtime'];
          	$global[$homeid][$eqLog_id]['nextprofil_delay'] = $getplanning['profils']['nextprofil_delay'];
          	$global[$homeid][$eqLog_id]['nextprofil_tendance'] = $nowprofil_temp-$nextprofil_temp;
          
          	//////////////////////////////////////////////////////////////////////////
			//						Parametres communs /Home						//
			//////////////////////////////////////////////////////////////////////////					
				
			foreach ($apiHome[$homeid]['Status']['rooms'] as $roomStatus) {
				if($roomStatus['id']==$roomid){
					//log::add('naEnergie', 'debug', ''.__FUNCTION__ .' roomStatus '.json_encode($roomStatus));
					$room_modetech = '';
					$consigne = '';
					
                  
                  	if (isset($roomStatus['therm_measured_temperature']) && $roomStatus['therm_measured_temperature']) {
						$global[$homeid][$eqLog_id]['temperature'] = $temperature = $roomStatus['therm_measured_temperature'];
					} else{
						$global[$homeid][$eqLog_id]['temperature'] = $temperature = $roomStatus['therm_measured_temperature']=99;
					}
                  
					if (isset($roomStatus['therm_setpoint_temperature']) && $roomStatus['therm_setpoint_temperature']) {
						$global[$homeid][$eqLog_id]['consigne'] = $consigne = $roomStatus['therm_setpoint_temperature'];
					} else{
						$global[$homeid][$eqLog_id]['consigne'] = $consigne = $roomStatus['therm_setpoint_temperature']=99;
					}
                  
                  	if($consigne > $temperature){
                    	$heatstatus = $global[$homeid][$eqLog_id]['requestOn'] = 1;
                      	$global[$homeid]['rooms_requestOn'][$roomid] = true;
                    }else{
                    	$heatstatus = $global[$homeid][$eqLog_id]['requestOn'] = 0;
                    }
                  	
					if (isset($roomStatus['open_window']) && $roomStatus['open_window'] != 0) {
						$global[$homeid][$eqLog_id]['open_window'] = $open_window = 1;
                      	$global[$homeid]['rooms_open_window'][$roomid] = true;
					} else{
						$global[$homeid][$eqLog_id]['open_window'] = $open_window = 0;
					}
					//$room_reachable = $roomStatus['reachable'];
					if(isset($roomStatus['reachable']) && $roomStatus['reachable'] != true){
						$eqLogic->setStatus('reachable_Nok', $eqLogic->getStatus('reachable_Nok', 0) + 1);
                      	$global[$homeid][$eqLog_id]['reachable'] = 1;
						if($eqLogic->getStatus('reachable_Nok', 0)===1){
							$eqLogic->setStatus('reachable_NokTime', date('d-m-Y H:i:s'));
						}
						//$eqLogic->save();
					}else{
						if($eqLogic->getStatus('reachable_Nok', 0) != 0){
							$eqLogic->setStatus('reachable_Nok', 0);
							$eqLogic->setStatus('reachable_NokTime', '');
                          	$global[$homeid][$eqLog_id]['reachable'] = 0;
						}
					}
                  
                  	if (isset($roomStatus['anticipating']) && $roomStatus['anticipating'] != false) {
						$global[$homeid][$eqLog_id]['anticipating'] = $room_anticipating = 1;
                      	$global[$homeid]['rooms_anticipating'][$roomid] = true;
                    } else{
						$global[$homeid][$eqLog_id]['anticipating'] = $room_anticipating=0;
                      	
					}
					//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' room_anticipating: '.$room_anticipating);
						
					//$fenetreOuverte=$roomStatus['open_window'];
						  
					if (isset($roomStatus['heating_power_request']) && $roomStatus['heating_power_request'] != 0) {
						$global[$homeid][$eqLog_id]['powerRequest'] = $powerRequest=1;
                      	$global[$homeid]['rooms_powerRequest'][$roomid] = $roomStatus['heating_power_request'];
					} else{
						$global[$homeid][$eqLog_id]['powerRequest'] = $powerRequest=0;
                      	
					}
					//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' ********* powerRequest: '.$powerRequest );  
						
					//$global[$homeid][$eqLog_id]['modetech'] = $modetech = $roomStatus['therm_setpoint_mode'];
					//$global[$homeid][$eqLog_id]['room_modetech'] = $room_modetech = ucfirst($modetech);
                  	$global[$homeid][$eqLog_id]['room_modetech'] = $room_modetech = $roomStatus['therm_setpoint_mode'];
                  	log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' ***** room_modetech: '.$room_modetech);
					
                  	if ($roomStatus['therm_setpoint_start_time'] != null){
                    	$global[$homeid][$eqLog_id]['setpointmode_starttime'] = $setpointmode_starttime=date('d-m-Y H:i',$roomStatus['therm_setpoint_start_time']);
                  	}else{
                    	$global[$homeid][$eqLog_id]['setpointmode_starttime'] = $setpointmode_starttime=null;
                    }
                  	//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' ********* setpointmode_starttime: '.$setpointmode_starttime);	
                  	
                  	if ($roomStatus['therm_setpoint_end_time'] != 0){
                    	$global[$homeid][$eqLog_id]['setpointmode_endtime'] = $setpointmode_endtime=date('d-m-Y H:i',$roomStatus['therm_setpoint_end_time']);
                  		//room_modetech=='hg' && 0 > 'Nouvel Ordre'
                    }else{
                    	switch ($room_modetech) {
                    		case 'schedule':$value=$profil_endtime; break;
                            case 'hg':$value='Nouvel Ordre'; break;
                      		default :$value='Nouvel Ordre'; break;
                        }
                      	$global[$homeid][$eqLog_id]['setpointmode_endtime'] = $setpointmode_endtime=$value;
                    }
                  	//log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' ********* setpointmode_endtime: '.$setpointmode_endtime);	
					if ($home_modetech == 'away') {
                        $statusname= 'Jusqu\'à ' . $setpointmode_endtime;
                    } else if ($home_modetech == 'hg') {
                        $statusname= 'Jusqu\'à ' . $setpointmode_endtime;
                    } else if ($home_modetech == 'max') {
                        $statusname= 'Jusqu\'à ' . $setpointmode_endtime;
                    } else if ($home_modetech == 'schedule') {
                        $statusname= $nowprofil . ' -> ' . $setpointmode_endtime.'-' . $nextprofil ;
                    } else if ($home_modetech == 'off') {
                        $statusname='Eteint';
                    } else {
                        $statusname='Jusqu\'à ' . $setpointmode_endtime;
                    }
                    ///////////////////////
                  //$eqLogic->setConfiguration('Joignable', $roomStatus['reachable']);
					$eqLogic->setConfiguration('Temperature', $roomStatus['therm_measured_temperature']);
					$eqLogic->setConfiguration('Consigne', $roomStatus['therm_setpoint_temperature']);
					$eqLogic->setConfiguration('spm_duaration',$sp_duration);
					//$sp_duration
					//$eqLogic->setConfiguration('Mode', $roomStatus['therm_setpoint_mode']);
					//$eqLogic->setConfiguration('DebutMode', $roomStatus['therm_setpoint_start_time']);
					//$eqLogic->setConfiguration('FinMode', $roomStatus['therm_setpoint_end_time']);
					//$eqLogic->setConfiguration('Anticipe', $roomStatus['anticipating']);
					$eqLogic->save();
				}	
			}
			//fin foreach ($Htatus['rooms'] as $roomStatus)
			log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' global: '.json_encode($global));
      		
          
          
          
          
          
          
          /////////FIN EXTRACTION /////////////////
       /////////Affectation des valeurs ///////////////// 
           
          	$eqmodules=$global[$homeid][$eqLog_id]['modules'];
          // log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' eqmodules '.json_encode($eqmodules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
          	//$eqLogic->batteryStatus(25);
			if(count($eqmodules) > 1){
				foreach ($eqmodules as $key=>$eqmodule) {
              		//$eqbatterie[]=self::batteryStrtoTaux($eqmodule['battery_state']);
                  	$eqbatterie[]=self::getBattery($eqmodule['battery_level'],$eqmodule['type']);
                  	$eqmodules[$key]['battery_percent']=self::getBattery($eqmodule['battery_level'],$eqmodule['type']);
                  	
                  	$eqrfstatus[]=$eqmodule['rf_strength'];
                  	$eqrftextstatus[]=self::getrfstate($eqmodule['rf_strength']);
                  	$eqmodules[$key]['rfpower']=self::getrfstate($eqmodule['rf_strength']);
                  	
                  //log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' rfpower '.$key.'--'. self::getrfstate($eqmodule['rf_strength']));
                  //log::add('naEnergie', 'debug', '	'.__FUNCTION__ .' eqmodules '.$eqmodules[$key]['rfpower']);
            	} 
				$batterie=min($eqbatterie);
              	$rfstatus=self::getrfstate(min($eqrfstatus));
            
            }else{
				//$batterie=self::batteryStrtoTaux($eqmodules[0]['battery_state']);
              	$batterie=self::getBattery($eqmodules[0]['battery_level'], $eqmodules[0]['type']);
              	$rfstatus=self::getrfstate($eqmodules[0]['rf_strength']);//$eqmodules[0]['rfstatus'];
              	$eqmodules[0]['rfpower']=$rfstatus;
              	$eqmodules[0]['battery_percent']=$batterie;
            }
          	$global[$homeid][$eqLog_id]['eqmodules']=json_encode($eqmodules, JSON_UNESCAPED_UNICODE);//$eqmodules;
           
          	$eqLogic->setConfiguration('batteryStatus', $batterie);
            $eqLogic->batteryStatus($batterie);
          	$eqLogic->setStatus('rfstatus', $rfstatus);
          	$global[$homeid][$eqLog_id]['batterie'] = $batterie;
          	$global[$homeid][$eqLog_id]['rfstatus'] = $rfstatus;
          
          	$roomCmdi = array('consigne', 'temperature', 'room_modetech', 'eqmodules', 'listprofil', 'nowprofil', 'nextprofil', 
                              'nextprofil_delay', 'nextprofil_tendance', 'setpointmode_starttime', 'setpointmode_endtime', 
                              'room_anticipating', 'requestOn', 'batterie', 'rfstatus', 'open_window', 'powerRequest');
          	
          	foreach ($roomCmdi as $rcmdi) {
              	if (array_key_exists( $rcmdi, $global[$homeid][$eqLog_id])) {
                    $cmd=$eqLogic->getCmd(null, $rcmdi);
                    $value=$global[$homeid][$eqLog_id][$rcmdi];
                    $eqLogic->checkAndUpdateCmd($cmd, $value);
					log::add('naEnergie','debug','    room-cmd('.$rcmdi.') set: to '. $value);
                }elseif (in_array($stat_moduleid, $global[$homeid][$eqLog_id]['modules'] ) 
                         && array_key_exists( $rcmdi, $global[$homeid][$eqLog_id]['modules'][$stat_moduleid])){//
                 	log::add('naEnergie','debug','    global-eqModule-AR: '.json_encode( $global[$homeid][$eqLog_id]['modules'] ));
                        	log::add('naEnergie','debug','    stat_moduleid '. $stat_moduleid);
                          	$value=$global[$homeid][$eqLog_id]['modules'][$stat_moduleid][$rcmdi];
                          	 log::add('naEnergie','debug','    room_module set: '.$rcmdi.' to '. $value);
                  	
                  log::add('naEnergie','debug','    room-cmd*mdle*('.$rcmdi.') set: to '. $value);
                }
            }
        
        
      		log::add('naEnergie','debug','		***********   fin eqlogiq '.($eqCount-1).' '.$eqLogic->getName().'  **********');
        	log::add('naEnergie','debug',''.PHP_EOL.'*'); 
			$eqCount++;
        }//fin foreach($eqLogics as $eqLogic
      	
      
      
      	$homeCmdi=array('home_modetech', 'listplanning', 'nowplanning', 'nowplanid', 'boiler_anticipating', 
                        'boiler_status', 'statusname', 'wifistatus', 'hg_temp', 'away_temp');
      	foreach($eqLogics as $eqLogic) {
        	$eqLog_id=$eqLogic->getLogicalId();
			list($roomid, $homeid) = explode('|', $eqLog_id);
          		
				log::add('naEnergie','debug','home-cmds for: '.$eqLogic->getName().' - '.$eqLog_id.PHP_EOL);  
			if (!array_key_exists( $homeid, $global)) {
              //continue;
            }
          	foreach ($homeCmdi as $cmdi) {
              	
				if (array_key_exists( $cmdi, $global[$homeid])) {
                  $cmd=$eqLogic->getCmd('info', $cmdi);
                  $value=$global[$homeid][$cmdi];
                  $eqLogic->checkAndUpdateCmd($cmd, $value);
                  log::add('naEnergie','debug','    home-cmd '.$cmdi.' set: to '. $value);
                }
            }
        	$eqLogic->clearCacheWidget();
            $eqLogic->refreshWidget();
        }
      	log::add('naEnergie','debug',''.PHP_EOL.'*'); 
      
      
      	self::writedataStat(json_encode($global, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'global');
      	self::writedataStat(json_encode($apiHome, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'apiHome');
      	self::$_global = $global;
      
      	//
	}

/////////////////////////set Manual mode	
	public function getCalendar($proglist, $roomid) {
      	//log::add('naEnergie','debug', '		'.__FUNCTION__ .' start... ');
		$nowplanning='';
		$nowplanid='';
		$nowprofil ='Aucun';
		$nextprofil ='Aucun';
		$setpointmode_endtime ='Nouvel Ordre';
		$listplanning='';
		$listprofil='';
	///////// Liste des plannings et planning en cours
		foreach ($proglist as $keyplan => $plan) {
			//$status=(isset($plan['selected'])) ? "TRUE" : "FALSE";//////<<? "YES" : "NO";
			if (isset($plan['selected']) && $plan['selected'] == TRUE){//////<<?
				//Planning actif
				$keyGoodplan=$keyplan;
				$nowplanning=$plan['name'];
				$nowplanid=$plan['id'];
              	$away_temp = $plan['away_temp'];
				$hg_temp = $plan['hg_temp'];
				$plan_type = $plan['type'];
			}
			$listplanning=$listplanning . $plan['id'] . '|' . $plan['name'] . ';';
		}
		$nowoffset=floor( (time()-date(strtotime('Monday this week')) )/60);//
		$nbtime=count($proglist[$keyGoodplan]["timetable"]);
		$actifplan=$proglist[$keyGoodplan];
      	
	///////// Profil actif et profil suivant $plan["timetable"][0]["zone_id"];
		//log::add('naEnergie', 'debug','		'.__FUNCTION__ .' actifplan : '.json_encode($actifplan));
      	foreach ($actifplan["timetable"] as $key => $time) {
			if ($time["m_offset"] >= $nowoffset){
				//Pendant la semaine
				$nextgoodkey = $key;
				$nextprofil_id = $time["zone_id"];
				$nowprofil_id = $actifplan["timetable"][$nextgoodkey-1]["zone_id"];
				$profil_offset = $time["m_offset"];
              	break;
			} elseif ($key == ($nbtime-1)){
				//Derniere plage de la semaine
              	$nowgoodkey = $nbtime-1;
				$nowprofil_id = $actifplan["timetable"][$nowgoodkey]["zone_id"];
				if($actifplan["timetable"][0]["id"]==$nowprofil_id){
					$nextprofil_id= $actifplan["timetable"][1]["zone_id"];
					$profil_offset = $actifplan["timetable"][1]["m_offset"]+7*24*60;
				}else{
					$nextprofil_id= $actifplan["timetable"][0]["zone_id"];
					$profil_offset = $actifplan["timetable"][0]["m_offset"]+7*24*60;
				}
			}
		}
		if ($profil_offset != 0) {
			$firstday=date(strtotime('Monday this week'));
		}else{//semaine prochaine
			$firstday=date(strtotime('Monday this week +6 days'));
		}
									
		$profil_endtime = date($firstday + $profil_offset*60);
		//log::add('naEnergie','debug', ''.__FUNCTION__ .' profil_endtime: '.$profil_endtime);
      	/*if ($profil_endtime == '') {
				$profil_endtime='00:00';
			}*/	
      
	///////// Liste des profils/profil actif-suivant/temp
		foreach ($actifplan["zones"] as $key => $zone) {
			foreach ($zone["rooms_temp"] as $key => $roomtemp) {
            	if($roomtemp["room_id"] == $roomid){
                	//log::add('naEnergie','debug', '		'.__FUNCTION__ .' roomid: '.$roomid.' # '.$roomtemp["room_id"].' -- '.$roomtemp['temp']);
              		$zone_temp=$roomtemp['temp'];
                    if ( isset($zone['name']) ) {	
                        $profilname = $zone['name'].';'.$zone_temp;
                    }
                  	else{
                        if ($zone['type'] == 0) {
                            $profilname = 'Home'.';'.$zone_temp;
                        }elseif ($zone['type'] == 1) {
                            $profilname = 'Nuit'.';'.$zone_temp;
                        }elseif ($zone['type'] == 3) {
                            $profilname = 'HG'.';'.$zone_temp;
                        }elseif ($zone['type'] == 5) {
                            $profilname = 'Absent'.';'.$zone_temp;
                        }else{
                            $profilname = 'Profile T° '.$zone_temp.';'.$zone_temp;
                        }
                    }
                    $listprofil=$listprofil.$profilname .'|';

                    ///////////////////////
                    if ($zone['id'] == $nowprofil_id) {
                        list($nowprofil, $nowprofil_temp) = explode(';', $profilname);
                    } elseif ($zone['id'] == $nextprofil_id) {
                        list($nextprofil, $nextprofil_temp) = explode(';', $profilname);
                    }
              	}//fin if($roomtemp["room_id"] == $roomid)
            }//fin foreach ($zone["rooms_temp"] as $key => $roomtemp
		}
		//fin foreach ($actifplan["zones"] as $key => $zone
      	//log::add('naEnergie','debug', '		'.__FUNCTION__ .' listprofil: '.$listprofil);
      	$planings['listplanning']=substr($listplanning, 0, -1);//$listplanning;
      	$planings['nowplanning']=$nowplanning;
		$planings['nowplanid']=$nowplanid;
		$planings['away_temp']=$away_temp;	
		$planings['hg_temp']=$hg_temp;//hg_temp	
		$planings['plan_type']=$plan_type;
		
      	$profils['listprofil']=substr($listprofil, 0, -1);//$listprofil;
		$profils['nowprofil']=$nowprofil;
		$profils['nowprofil_temp']=$nowprofil_temp;
		$profils['nextprofil']=$nextprofil;
		$profils['nextprofil_temp']=$nextprofil_temp;
		$profils['profil_endtime']=date('d-m-Y H:i', $profil_endtime);
      	$profils['nextprofil_delay']=floor(($profil_endtime-time())/60);//$profil_endtime-time();
      	
		return array('planings'=>$planings, 'profils'=>$profils );
      
      
	}
  
//////////////////////////////////////
	public static function getBattery($battery_vp, $type) {
		
		if($type == 'NRV'){
          	$bat_min =2000;
            $bat_max =3500;  
        }elseif($type == 'NATherm1'){
          $bat_min =2750;//3000
          $bat_max =4800;//4500
        }
      	$value=round(($battery_vp - $bat_min)*100/($bat_max - $bat_min), 0);
      	//$value= round(100-($bat_max-$battery_vp)*98/$bat_min, 0);
      	
      	//log::add('naEnergie', 'debug', __FUNCTION__ .' '. $battery_vp.' = '.$value.'%');
		if($value > 100) return 100;
      	elseif($value < 0) return 1;
        else return $value;
	}
///////////////////////////////	
	public static function getrfstate($signal) {
		if($signal >= 90) return 'very_low';
		if($signal >= 80) return 'low';
		if($signal >= 70) return 'medium';
		if($signal >= 60) return 'high';
		return 'full';
	}
///////////////////////////////		
	public static function getWifi($wifi) {
		if($wifi >= 100)return 'wifi_unknown.png';
		if($wifi >= 86) return 'wifi_low.png';    
		if($wifi >= 71) return 'wifi_medium.png';
		if($wifi >= 56) return 'wifi_high.png';   
		return 'wifi_full.png';
	}	
///////////////////////////////		
	public static function getNaType($type) {
		switch($type)
		
		{	//Thermostat
			case "NATherm1": return $type = 'Thermostat';break;
			//Plug du Thermostat
			case "NAPlug": return $type = 'Relay_TH';break;
			//Valve
			case "NRV": return $type = 'Valve';break;
			//Indoor device meteo
			case "NAMain": return $type = 'Station';break;
			// Outdoor Module
			case "NAModule1": return $type = 'Module extérieur';break;
			//Wind Sensor
			case "NAModule2": return $type = 'Anémomètre';break;
			//Rain Gauge
			case "NAModule3": return $type = 'Pluviomètre';break;
			//Indoor Module
			case "NAModule4": return $type = 'Module_int';break;
		}		
	}
//////////////////////////////////////
	
///////////////////////////////		
	public static function getRoomType($roomType) {
		switch($roomType)
		{	//Indoor device meteo
			case "lobby": return $roomType = 'Entr/ée';break;
			// Outdoor Module
			case "bathroom": return $roomType = 'Salle de bains';break;
			//Wind Sensor
			case "bedroom": return $roomType = 'Chambre';break;
			//Rain Gauge
			case "custom": return $roomType = 'Autre';break;
			//Indoor Module
			case "garage": return $roomType = 'Garage';break;
			//Indoor Module
			case "kitchen": return $roomType = 'Cuisine';break;
			//Indoor Module
			case "livingroom": return $roomType = 'Salon';break;
			//Valve
			case "toilet": return $roomType = 'Toilette';break;
		}		
	}
/////////////////////////////////////*********************///////////////////////////////////// 
    public static function removeAll(){
        log::add('naEnergie', 'debug', __FUNCTION__ . ' start ');
        $eqLogics = eqLogic::byType('naEnergie', false);
        foreach ($eqLogics as $eqLogic) {
            $eqLogic->remove();
        }
        //config::remove('macBox', __CLASS__);
      	//cache::set('naEnergie' . '_token_auth' , null);
        //cache::set('naEnergie' . '_token_time' , null);
        return array(true,'remove ok');//'remove ok'
    }
  
  
////////////////////////////////cronDaily() 
/*
 * public static function cronExtmod() {
		log::add('naEnergie', 'debug', 'Starting '.__FUNCTION__ .'********');
		$path = dirname(__FILE__) . '/../../data';
		if (!is_dir($path)) {
			 log::add('naEnergie', 'debug', 'data2 existe pas');
			com_shell::execute(system::getCmdSudo() . 'mkdir ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			com_shell::execute(system::getCmdSudo() . 'chmod 777 -R ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			 log::add('naEnergie', 'debug', 'data crééé');
		} else {
			 log::add('naEnergie', 'debug', 'data existe ');
			com_shell::execute(system::getCmdSudo() . 'chmod 777 -R ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			 log::add('naEnergie', 'debug', 'droit sudo');
		}		  
		
		
		$client = self::getClient(__FUNCTION__);
		$datas = array();
		$files = array('year','month');
		$eqLogics = eqLogic::byType('naEnergie');
		  //$eqLogics = eqLogic::byLogicalId('naEnergie');
		$j = 0;
		foreach ($eqLogics as $eqLogic) {
			if ($eqLogic->getIsEnable()) {
				log::add('naEnergie', 'debug', 'year '.$eqLogic->getName());
				$scale = array('1month','1day');
				$k=0;
				
				for ($i = 0; $i <= 2 ; $i++) {
					$year = date('Y') - $i;
					$begin = new DateTime('first day of January ' . $year);
					$begin= $begin->getTimestamp();
					
					$end = new DateTime('last day of December ' . $year);
					$end = $end->getTimestamp() + 23*3600;	
					
					$type = $eqLogic->getConfiguration('type');
					//$md=$eqLogic->getConfiguration('main_deviceId');
					
					$datas['Data_date']=date('d-m-Y H:i:s');
					$datas['name'] = $eqLogic->getName() ;
					$datas['type'] = $type;
					
					switch($type) {
							case 'NAMain'://device WS
								$datatype='Temperature,Humidity,Pressure,Noise,CO2,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measurey = $client->_getMeasure($eqLogic->getLogicalId(),NULL, '1month', $datatype , $begin, $end, 1024, FALSE, FALSE);
								$measurem = $client->_getMeasure($eqLogic->getLogicalId(),NULL, '1day', $datatype , $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getLogicalId() ;
								$datas['module_id'] = $eqLogic->getLogicalId() ;
								
								break;
							case 'NAModule1'://module ext WS
								$datatype='Temperature,Humidity,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measurey = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1month', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$measurem = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1day', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas['module_id'] = $eqLogic->getLogicalId();
								$datam=$datas;
								break;
							case 'NAModule4'://Indoor Module sup
								$datatype='Temperature,Humidity,CO2,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measurey = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1month', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$measurem = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1day', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas['module_id'] = $eqLogic->getLogicalId();
								$datam=$datas;
								break;
							
							case 'NAModule2'://Wind Sensor
								$datatype='WindStrength,WindAngle,GustStrength,GustAngle,date_max_gust';
								$measurey = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1month', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$measurem = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1day', $datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas['module_id'] = $eqLogic->getLogicalId();
								$datam=$datas;
								break;
							case 'NATherm1'://module TH
								$clientTh = self::getClient(__FUNCTION__,'NATherm1');
								$datatype='Temperature,Sp_Temperature,BoilerOn,BoilerOff,date_max_temp,max_temp,min_temp,date_min_temp';
								$measurey = $clientTh->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1month',$datatype, $begin, $end, 1024, FALSE, FALSE);
								$measurem = $clientTh->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1day',$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas['module_id'] = $eqLogic->getLogicalId();
								$datam=$datas;
								break;
							case 'NAModule3'://Rain Gauge
								$datatype='Rain,sum_rain';
								$measurey = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1month',$datatype, $begin, $end, 1024, FALSE, FALSE);
								$measurem = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), '1day',$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas['module_id'] = $eqLogic->getLogicalId();
								$datam=$datas;
								break;	
								
				}  //fin  swich
						
							if ($measurey != null && $measurey != '') {
								
								//$datay[$j]=$datas[$j];
								//$datay[$j]['year'][$year] = $measurey[$year];
								$datas['year'][$year] = $measurey;
								
								log::add('naEnergie','debug', 'cron_datats 4 '.json_encode($datas[$j]['year']));
							} else {break;}
							if ($measurem != null && $measurem != '') {
								//$datam[$j]=$datas[$j];
								$datam['year'][$year] = $measurem;
								
							} else {break;}
							
							//log::add('naEnergie','debug', 'cron_datats 2 Sj '.$j.'|'.$type.'|'.json_encode($datas[$j]));
							
				}//Fin for
				
				
			}//fin if
			//log::add('naEnergie','debug', 'cron_datats 3 '.$type.'|'.json_encode($datas));
		
						
		
		}//fin foreach ($eqLogics as $eqLogic) {
		
		file_put_contents(dirname(__FILE__) . '/../../data/year' .'.json', json_encode($datas));				
		file_put_contents(dirname(__FILE__) . '/../../data/month' .'.json', json_encode($datam));
		
		
				
				
			log::add('naEnergie', 'debug', 'Fin '.__FUNCTION__ .'********');
		 		
	}//fin function cronExt
	
	
*/				
		
	public static function cronDaily() {
		//**************$client = self::getClient(__FUNCTION__);
		//**************self::cronExt();
	}
	public static function cron15() {
		
      	log::add('naEnergie', 'info', __FUNCTION__ .' started *****************');
		try {
		
			self::getDataRoom(null);//, $nbcron
		} 
		catch (Exception $e) {	
			log::add('naEnergie', 'debug', __('Erreur sur ', __FILE__) . ' : ' . $e->getMessage());
		}
		
		///////////////////	
		$maxTimeUpdate="45";//min
		$nbcron=5;
		$eqLogics = eqLogic::byType('naEnergie', true);
		foreach ($eqLogics as $eqLogic) {
          	$type=$eqLogic->getConfiguration('type');
            if ($type=='home') {
                continue;
            }
			//$cron = cron::byClassAndFunction('naEnergie', 'pull', $_options);
			try {
                //event::add('jeedom::alert', array('level' => 'danger', 'page' => 'naEnergie', 'message' => __('Impossible de contacter le serveur ', __FILE__) . $server['name']));
                $plugUptime=$eqLogic->getStatus('lastCommunication');
                if ($eqLogic->getStatus('reachable_Nok', 0)>0) {
                    $eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
                    $eqLogic->save();
                    log::add('naEnergie', 'error', 'Erreur du '.$eqLogic->getHumanName() . ' : Attention, il n\'y a pas eu de mise à jour des données depuis : '.  date("d-m-Y H:i", strtotime($plugUptime)) .' Vérifier la connexion du plugin ');
                }else {
                    $eqLogic->setConfiguration('croncount', $eqLogic->getConfiguration('croncount', 0)+1);
                    $eqLogic->setConfiguration('UpNumberFailed', 0);
                    if($eqLogic->getConfiguration('croncount', 0) >= $nbcron){
                        $eqLogic->setConfiguration('croncount', 0);
                    }
                    $eqLogic->save();
                }
			} 
			catch (Exception $e) {
					$eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
					$eqLogic->save();
					
						$cmd = $eqLogic->getCmd(null, 'temperature');
						//$temp_in = $cmd->execCmd();
						$coltime=$cmd->getCollectDate();

					if ($eqLogic->getConfiguration('UpNumberFailed', 0) > 12 
                        && date('d-m-Y H:i:s') > date('d-m-Y H:i:s', strtotime("$coltime + $maxTimeUpdate minutes"))) {
						log::add('naEnergie', 'error', 'Erreur '.$eqLogic->getHumanName() . __(' : Attention, il n\'y a pas eu de mise à jour des données depuis : ', __FILE__) .  date("d-m-Y H:i", strtotime($coltime)) .' min');
						log::add('naEnergie', 'debug', __('Erreur sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
		
					}else {
						log::add('naEnergie', 'debug', __('Erreur' .$eqLogic->getConfiguration('UpNumberFailed', 0) .'sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					}
				}
		}
		log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
	}
/////////////////////////////////////////
///////////////////////////////	
	public static function cronHourly1($gateway=null) {//cron cronHourly
		log::add('naEnergie', 'info', __FUNCTION__ .' started *****************');
		//$eqLogic = eqLogic::byType('naEnergie');
		$maxTimeUpdate="300";//min sinon erreur
		try {
			naEnergie::getDataRoom($gateway);
			
			
			//naEnergie::cronExt();
		}
		catch (Exception $e) {
			log::add('naEnergie', 'debug', __('Erreur cron getDataRoom  ', __FILE__)  . $e->getMessage());
		}
		
		foreach (naEnergie::byType('naEnergie', true) as $eqLogic) {
			if ($eqLogic->getConfiguration('read_only')!=1) {
				//$cron = cron::byClassAndFunction('naEnergie', 'pull', $_options);
				try {
						//$uptime=$eqLogic->getConfiguration(dash_data['time_utc']);
						$duptime=date('d-m-Y H:i:s', $eqLogic->getConfiguration('dash_data')['time_utc']);
						$uptime=$eqLogic->getStatus('lastCommunication');
						//log::add('naEnergie', 'debug','duptime: '.$duptime.' '.$uptime);
						
						$struptime=date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"));
						if (date('d-m-Y H:i:s') > date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"))) {
							$eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
							
							log::add('naEnergie', 'error', 'Erreur de '.$eqLogic->getHumanName() . ' : Attention, il n\'y a pas eu de mise à jour des données depuis : '.  date("d-m-Y H:i", strtotime($uptime)) .' min'.' verifier la connectivité des appareils ');
							log::add('naEnergie', 'debug', '			    vérifier la connectivité des appareils !'.$duptime.':'.$eqLogic->getConfiguration('UpNumberFailed'));
							$eqLogic->save();
						}else{
							$eqLogic->setConfiguration('UpNumberFailed',0);
							$eqLogic->save();
						}							
				////////////////////////////////////
					
				} 
				catch (Exception $e) {
					$eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
					$eqLogic->save();
						//$probefail=$eqLogic->getCache('probe_failure', 0);
						$uptime=$eqLogic->getStatus('lastCommunication');
						$struptime=date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"));
					if ($eqLogic->getConfiguration('UpNumberFailed', 0) > 12 && date('d-m-Y H:i:s') > date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"))) {
						log::add('naEnergie', 'error', 'Erreur 3 '.$eqLogic->getHumanName() . ' : Attention, il n\'y a pas eu de mise à jour des données depuis : '.  date("d-m-Y H:i", strtotime($uptime)) .' min');
						log::add('naEnergie', 'debug', __('Erreur 10 sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					}else {
						log::add('naEnergie', 'debug', __('Erreur 4' .$eqLogic->getConfiguration('UpNumberFailed', 0) .'sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					}
				}
			}
		}
		
		
		log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
	}
///////////////////////////////	
	public static function cronHourly0() {//cron cronHourly
		log::add('naEnergie', 'info', __FUNCTION__ .' started *****************');
		//$eqLogic = eqLogic::byType('naEnergie');
		$maxTimeUpdate="300";//min sinon erreur
		try {
			//**************naEnergie::getDataRoom();
			//naEnergie::cronExt();
		}
		catch (Exception $e) {
			log::add('naEnergie', 'debug', __('Erreur cron getDataRoom  ', __FILE__)  . $e->getMessage());
		}
		
		foreach (naEnergie::byType('naEnergie', true) as $eqLogic) {
			if ($eqLogic->getConfiguration('read_only')!=1) {
				//$cron = cron::byClassAndFunction('naEnergie', 'pull', $_options);
				try {
						//$uptime=$eqLogic->getConfiguration(dash_data['time_utc']);
						$duptime=date('d-m-Y H:i:s', $eqLogic->getConfiguration('dash_data')['time_utc']);
						$uptime=$eqLogic->getStatus('lastCommunication');
						//log::add('naEnergie', 'debug','duptime: '.$duptime.' '.$uptime);
						
						$struptime=date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"));
						if (date('d-m-Y H:i:s') > date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"))) {
							$eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
							
							log::add('naEnergie', 'error', 'Erreur de '.$eqLogic->getHumanName() . ' : Attention, il n\'y a pas eu de mise à jour des données depuis : '.  date("d-m-Y H:i", strtotime($uptime)) .' min'.' verifier la connectivité des appareils ');
							log::add('naEnergie', 'debug', '			    vérifier la connectivité des appareils !'.$duptime.':'.$eqLogic->getConfiguration('UpNumberFailed'));
							$eqLogic->save();
						}else{
							$eqLogic->setConfiguration('UpNumberFailed',0);
							$eqLogic->save();
						}							
				////////////////////////////////////
					
				} 
				catch (Exception $e) {
					$eqLogic->setConfiguration('UpNumberFailed', $eqLogic->getConfiguration('UpNumberFailed', 0) + 1);
					$eqLogic->save();
						//$probefail=$eqLogic->getCache('probe_failure', 0);
						$uptime=$eqLogic->getStatus('lastCommunication');
						$struptime=date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"));
					if ($eqLogic->getConfiguration('UpNumberFailed', 0) > 12 && date('d-m-Y H:i:s') > date('d-m-Y H:i:s', strtotime("$uptime + $maxTimeUpdate minutes"))) {
						log::add('naEnergie', 'error', 'Erreur 3 '.$eqLogic->getHumanName() . ' : Attention, il n\'y a pas eu de mise à jour des données depuis : '.  date("d-m-Y H:i", strtotime($uptime)) .' min');
						log::add('naEnergie', 'debug', __('Erreur 10 sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					}else {
						log::add('naEnergie', 'debug', __('Erreur 4' .$eqLogic->getConfiguration('UpNumberFailed', 0) .'sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					}
				}
			}
		}
		log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
	}

    /*     * *********************Méthodes d'instance************************* */


/////////////////////////////////////////
	public function postSave() {
		$type=$this->getConfiguration('type');
      	if ($type=='home') {
        	return;
        }
      	log::add('naEnergie', 'debug', '    '.__FUNCTION__ .'  *****');
      	list($roomid,$homeid) = explode('|', $this->getLogicalId());
        
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'consigne');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				
				$naEnergiecmd->setUnite('°C');
				$naEnergiecmd->setName(__('Consigne', __FILE__));
				$naEnergiecmd->setConfiguration('historizeMode', 'none');
              	$naEnergiecmd->setConfiguration('cmdType', 'room');
              	$naEnergiecmd->setConfiguration('roomid', $roomid);
				$naEnergiecmd->setIsHistorized(1);
              	$naEnergiecmd->setIsVisible(0);
			}
			$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_SETPOINT');
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
			$naEnergiecmd->setLogicalId('consigne');
      		$naEnergiecmd->setOrder(1);
			//$naEnergiecmd->setConfiguration('maxValue', $this->getConfiguration('order_max'));
			//$naEnergiecmd->setConfiguration('minValue', $this->getConfiguration('order_min'));
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'temperature');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Température', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
              	$naEnergiecmd->setConfiguration('roomid', $roomid);
              	$naEnergiecmd->setIsHistorized(1);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('temperature');
			$naEnergiecmd->setUnite('°C');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
			$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_TEMPERATURE');
      		$naEnergiecmd->setOrder(2);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'room_modetech');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('ModeTech (Room)', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
              	$naEnergiecmd->setConfiguration('roomid', $roomid);
            }
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('room_modetech');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
      		$naEnergiecmd->setOrder(3);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'home_modetech');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Mode Thermostat ModeTech (Home)', __FILE__));
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('home_modetech');
      		$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_MODE');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
      		$naEnergiecmd->setOrder(5);
			$naEnergiecmd->save();
		// cmdinfo:  $eqmodules******************************** //
			$naEnergiecmd = $this->getCmd(null, 'eqmodules');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Infos Modules', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
            }
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('eqmodules');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			//$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_MODE');
      		$naEnergiecmd->setOrder(7);$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'listplanning');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Liste Planning', __FILE__));
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('listplanning');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(10);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'nowplanning');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Planning en cours', __FILE__));
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nowplanning');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(11);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'nowplanid');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Id planning en cours', __FILE__));
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nowplanid');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(12);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'listprofil');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Liste Profil', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
            }
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('listprofil');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(13);$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'nowprofil');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Profile de température', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nowprofil');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(14);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'nextprofil');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Profile suivant', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nextprofil');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(15);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'nextprofil_delay');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Délai prochain profile', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
				$naEnergiecmd->setIsHistorized(0);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nextprofil_delay');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
      		$naEnergiecmd->setUnite('min');
			$naEnergiecmd->setOrder(16);
			$naEnergiecmd->save();
      	// cmdinfo:  ******************************** //
      		$naEnergiecmd = $this->getCmd(null, 'nextprofil_tendance');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Tendance prochain profile', __FILE__));
				$naEnergiecmd->setIsHistorized(0);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('nextprofil_tendance');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
      		$naEnergiecmd->setUnite('°C');
			$naEnergiecmd->setOrder(16);
			$naEnergiecmd->save();
      // cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'setpointmode_starttime');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Début Mode en Cours', __FILE__));
				$naEnergiecmd->setIsHistorized(0);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('setpointmode_starttime');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
      		$naEnergiecmd->setConfiguration('isdate',true);
      		$naEnergiecmd->setOrder(17);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'setpointmode_endtime');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Fin Mode en Cours', __FILE__));
				$naEnergiecmd->setIsHistorized(0);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('setpointmode_endtime');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
      		$naEnergiecmd->setConfiguration('isdate',true);
			$naEnergiecmd->setOrder(18);
			$naEnergiecmd->save();
      // cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'room_anticipating');//anticipation
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Anticipation en cours(Room)', __FILE__));
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('room_anticipating');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('binary');
			$naEnergiecmd->setOrder(20);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'boiler_anticipating');//boileranticip
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Anticipation Chaudiere(Home)', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('boiler_anticipating');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('binary');
			//$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE');
			$naEnergiecmd->setOrder(21);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'boiler_status');//boilerstate
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Etat Chaudiere (Home)', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('boiler_status');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('binary');
			$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE');
			$naEnergiecmd->setOrder(22);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'requestOn');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('requestOn (Room)', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('requestOn');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('binary');
			$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE');
			$naEnergiecmd->setOrder(23);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'statusname');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Statut pour mobile', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('statusname');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE_NAME');
			$naEnergiecmd->setOrder(24);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'hg_temp');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('T° Hors-gel', __FILE__));
				$naEnergiecmd->setIsHistorized(0);
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('hg_temp');
			$naEnergiecmd->setUnite('°C');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
			//$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_TEMPERATURE');
			$naEnergiecmd->setOrder(25);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'away_temp');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('T° Absence', __FILE__));
				$naEnergiecmd->setIsHistorized(0);
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('away_temp');
			$naEnergiecmd->setUnite('°C');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
			//$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_TEMPERATURE');
			$naEnergiecmd->setOrder(26);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'wifistatus');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Signal Wifi', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
              	$naEnergiecmd->setConfiguration('cmdType', 'home');
            	$naEnergiecmd->setConfiguration('homeid', $homeid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('wifistatus');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setUnite('%');
			$naEnergiecmd->setSubType('numeric');
			$naEnergiecmd->setOrder(30);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'rfstatus');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Signal RF', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('rfstatus');
			$naEnergiecmd->setUnite('%');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('string');
			$naEnergiecmd->setOrder(31);
			$naEnergiecmd->save();
		// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'batterie');
			if (!is_object($naEnergiecmd)) {
				$naEnergiecmd = new naEnergiecmd();
				$naEnergiecmd->setName(__('Batterie', __FILE__));
				$naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
			}
			$naEnergiecmd->setEqLogic_id($this->getId());
			$naEnergiecmd->setLogicalId('batterie');
			$naEnergiecmd->setType('info');
			$naEnergiecmd->setSubType('numeric');
			$naEnergiecmd->setOrder(32);
			$naEnergiecmd->save();
		
		
      		
      
      	if ($this->getConfiguration('type')== 'NRV' ) {
			// cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'open_window');
            if (!is_object($naEnergiecmd)) {
                $naEnergiecmd = new naEnergiecmd();
                $naEnergiecmd->setName(__('Fenetre ouverte', __FILE__));
                $naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
            }
           	$naEnergiecmd->setEqLogic_id($this->getId());
            $naEnergiecmd->setLogicalId('open_window');
            $naEnergiecmd->setType('info');
            $naEnergiecmd->setSubType('binary');
            //$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE');
            $naEnergiecmd->setOrder(41);
			$naEnergiecmd->save();
        // cmdinfo:  ******************************** //
			$naEnergiecmd = $this->getCmd(null, 'powerRequest');
            if (!is_object($naEnergiecmd)) {
                $naEnergiecmd = new naEnergiecmd();
                $naEnergiecmd->setName(__('Ouverture vanne', __FILE__));
                $naEnergiecmd->setIsHistorized(1);
				$naEnergiecmd->setConfiguration('cmdType', 'room');
            	$naEnergiecmd->setConfiguration('roomid', $roomid);
            }
            $naEnergiecmd->setEqLogic_id($this->getId());
            $naEnergiecmd->setLogicalId('powerRequest');
            $naEnergiecmd->setType('info');
      		$naEnergiecmd->setUnite('%');
            $naEnergiecmd->setSubType('numeric');
            //$naEnergiecmd->setDisplay('generic_type', 'THERMOSTAT_STATE');
            $naEnergiecmd->setOrder(42);
			$naEnergiecmd->save();  
        
        
        
        
        }//fin if ($this->getConfiguration('type')== 'NRV'
      
      
      	/*---------------------------------------------------------------*/	
      	////////////////////////// CMD Actions ////////////////////////////
     	/*---------------------------------------------------------------*/	
      		
      		$consigneset = $this->getCmd(null, 'consigneset');
            if (!is_object($consigneset)) {
                $consigneset = new naEnergiecmd();
                $consigneset->setLogicalId('consigneset');
                $consigneset->setIsVisible(1);
                $consigneset->setName(__('Réglage Consigne (Room)', __FILE__));
            }
            $consigneset->setType('action');
			//$consigneset->setDisplay('title_placeholder', __('Température', __FILE__));
			//$consigneset->setDisplay('message_placeholder', __('Durée (minutes)', __FILE__));
			$consigneset->setSubType('slider');
            $consigneset->setEqLogic_id($this->getId());
      		$consigneset->setUnite('°C');
      		$consigneset->setConfiguration('minValue', 7);//
			$consigneset->setConfiguration('maxValue', 30);//
      		$consigneset->setTemplate('dashboard', 'consigneset');//
            $consigneset->setTemplate('mobile', 'consigneset');//
            $consigneset->setOrder(61);
			$consigneset->save();
		// cmdAction:  ******************************** //
			$homeboost = $this->getCmd(null, 'homeboost');
            if (!is_object($homeboost)) {
                $homeboost = new naEnergiecmd();
                $homeboost->setLogicalId('homeboost');
                $homeboost->setIsVisible(1);
                $homeboost->setName(__('Manual boost (Home)', __FILE__));
                $homeboost->setTemplate('dashboard', 'naEnergie');//
                $homeboost->setTemplate('mobile', 'naEnergie');//
				$homeboost->setUnite('°C');
            }
            $homeboost->setType('action');
			$homeboost->setSubType('slider');
			$homeboost->setConfiguration('minValue', 7);//
			$homeboost->setConfiguration('maxValue', 30);//
            $homeboost->setEqLogic_id($this->getId());
            $homeboost->setUnite('°C');
			$homeboost->setGeneric_type('THERMOSTAT_SET_SETPOINT');
			$homeboost->setOrder(62);
			//$homeboost->setValue($consigneset->getId());
            $homeboost->save();
		// cmdAction:  ******************************** //
			$planningset = $this->getCmd(null, 'planningset');
            if (!is_object($planningset)) {
                $planningset = new naEnergiecmd();
                $planningset->setLogicalId('planningset');
                $planningset->setIsVisible(1);
                $planningset->setName(__('Changer Planning (Home)', __FILE__));
            }
            $planningset->setEqLogic_id($this->getId());
      		$planningset->setType('action');
			$planningset->setSubType('select');
      		$planningset->setConfiguration('listValue',  $this->getCmd(null, 'listplanning')->execCmd());
      		$planningset->setValue($this->getCmd(null, 'nowplanid')->getId() );
      		//$planningset->setValue(self::getPlanningId($this->getCmd(null, 'planning')->execCmd()) );
      		//$planningset->setDisplay('title_disable', 1);
			//$planningset->setDisplay('message_placeholder', __('Id du planning', __FILE__));
			//$planningset->setSubType('message');
            $planningset->setOrder(63);
			$planningset->save();
		// cmdAction:  ******************************** //
			$dureeset = $this->getCmd(null, 'dureeset');
            if (!is_object($dureeset)) {
                $dureeset = new naEnergiecmd();
                $dureeset->setLogicalId('dureeset');
                $dureeset->setIsVisible(1);
                $dureeset->setName(__('Réglage mode et/ou fin du mode', __FILE__));
            }
            $dureeset->setType('action');
			//$dureeset->setDisplay('title_disable', 1);
      		$dureeset->setDisplay('title_placeholder', "Mode (optionel)");
      		$dureeset->setDisplay('message_disable', 1);
      		$dureeset->setDisplay('message_placeholder', __('Timestamp de fin', __FILE__));
      		$dureeset->setSubType('message');
      		$dureeset->setConfiguration('listValue', "hg|Hors-Gel; away|Absent; schedule|Planning; off|Off; max|Forcé");
      
            $dureeset->setEqLogic_id($this->getId());
            $dureeset->setOrder(64);
			$dureeset->save();
		// cmdAction:  ******************************** //
			$roomAuto = $this->getCmd(null, 'roomAuto');
            if (!is_object($roomAuto)) {
                $roomAuto = new naEnergiecmd();
                $roomAuto->setLogicalId('roomAuto');
                $roomAuto->setIsVisible(1);
                $roomAuto->setName(__('Mode Auto (Piéce)', __FILE__));
            }
            $roomAuto->setType('action');
            $roomAuto->setSubType('other');
			$roomAuto->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $roomAuto->setEqLogic_id($this->getId());
            $roomAuto->setOrder(65);
			$roomAuto->save();
		// cmdAction:  ******************************** //
			$homeAuto = $this->getCmd(null, 'homeAuto');
            if (!is_object($homeAuto)) {
                $homeAuto = new naEnergiecmd();
                $homeAuto->setLogicalId('homeAuto');
                $homeAuto->setIsVisible(1);
                $homeAuto->setName(__('Mode Auto (Home) ', __FILE__));
            }
            $homeAuto->setType('action');
            $homeAuto->setSubType('other');
			$homeAuto->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $homeAuto->setEqLogic_id($this->getId());
            $homeAuto->setOrder(66);
      		$homeAuto->save();
		// cmdAction:  ******************************** //
			$setaway = $this->getCmd(null, 'setaway');
            if (!is_object($setaway)) {
                $setaway = new naEnergiecmd();
                $setaway->setLogicalId('setaway');
                $setaway->setIsVisible(1);
                $setaway->setName(__('Absent (Home)', __FILE__));
            }
            $setaway->setType('action');
			$setaway->setDisplay('title_disable', 1);
			$setaway->setDisplay('message_placeholder', __('Durée (minutes)', __FILE__));
			$setaway->setSubType('message');
            $setaway->setEqLogic_id($this->getId());
            $setaway->setOrder(67);
			$setaway->save();
		// cmdAction:  ******************************** //
			$sethg = $this->getCmd(null, 'sethg');
            if (!is_object($sethg)) {
                $sethg = new naEnergiecmd();
                $sethg->setLogicalId('sethg');
                $sethg->setIsVisible(1);
                $sethg->setName(__('Hors-gel (Home)', __FILE__));
            }
            $sethg->setType('action');
			$sethg->setDisplay('title_disable', 1);
			$sethg->setDisplay('message_placeholder', __('Durée (minutes)', __FILE__));
			$sethg->setSubType('message');
            $sethg->setEqLogic_id($this->getId());
            $sethg->setOrder(68);
			$sethg->save();
		// cmdAction:  ******************************** //
			$setoff = $this->getCmd(null, 'setoff');
            if (!is_object($setoff)) {
                $setoff = new naEnergiecmd();
                $setoff->setLogicalId('setoff');
                $setoff->setIsVisible(1);
                $setoff->setName(__('Eteindre (Room)', __FILE__));
            }
            $setoff->setType('action');
            $setoff->setSubType('other');
			$setoff->setEqLogic_id($this->getId());
			$setoff->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $setoff->setOrder(69);
            $setoff->save();
		// cmdAction:  ******************************** //
			$setmax = $this->getCmd(null, 'setmax');
            if (!is_object($setmax)) {
                $setmax = new naEnergiecmd();
                $setmax->setLogicalId('setmax');
                $setmax->setIsVisible(1);
                $setmax->setName(__('Mode Forcé (Room)', __FILE__));
            }
            $setmax->setType('action');
			$setmax->setDisplay('title_disable', 1);
			$setmax->setDisplay('message_placeholder', __('Durée (minutes)', __FILE__));
			$setmax->setSubType('message');
            $setmax->setEqLogic_id($this->getId());
            $setmax->setOrder(70);
			$setmax->save();
		// cmdAction:  ******************************** //
			$awaymobile = $this->getCmd(null, 'awaymobile');
            if (!is_object($awaymobile)) {
                $awaymobile = new naEnergiecmd();
                $awaymobile->setLogicalId('awaymobile');
                $awaymobile->setIsVisible(1);
                $awaymobile->setName(__('Absent appmobile (Home)', __FILE__));
            }
            $awaymobile->setType('action');
            $awaymobile->setSubType('other');
			$awaymobile->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $awaymobile->setEqLogic_id($this->getId());
            $awaymobile->setOrder(71);
			$awaymobile->save();
		// cmdAction:  ******************************** //
			$hgmobile = $this->getCmd(null, 'hgmobile');
            if (!is_object($hgmobile)) {
                $hgmobile = new naEnergiecmd();
                $hgmobile->setLogicalId('hgmobile');
                $hgmobile->setIsVisible(1);
                $hgmobile->setName(__('Hors-gel mobile (Home)', __FILE__));
            }
            $hgmobile->setType('action');
            $hgmobile->setSubType('other');
			$hgmobile->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $hgmobile->setEqLogic_id($this->getId());
            $hgmobile->setOrder(72);
			$hgmobile->save();
		// cmdAction:  ******************************** //
			$maxmobile = $this->getCmd(null, 'maxmobile');
            if (!is_object($maxmobile)) {
                $maxmobile = new naEnergiecmd();
                $maxmobile->setLogicalId('maxmobile');
                $maxmobile->setIsVisible(1);
                $maxmobile->setName(__('Mode Forcé pour appmobile (Room)', __FILE__));
            }
            $maxmobile->setType('action');
            $maxmobile->setSubType('other');
			$maxmobile->setDisplay('generic_type', 'THERMOSTAT_SET_MODE');
            $maxmobile->setEqLogic_id($this->getId());
            $maxmobile->setOrder(73);
			$maxmobile->save();
		// cmdAction:  ******************************** //
			$consignesetmobile = $this->getCmd(null, 'consignemobile');
            if (!is_object($consignesetmobile)) {
                $consignesetmobile = new naEnergiecmd();
                $consignesetmobile->setLogicalId('consignemobile');
                $consignesetmobile->setIsVisible(1);
                $consignesetmobile->setName(__('Consigne pour appmobile', __FILE__));
            }
            $consignesetmobile->setType('action');
			$consignesetmobile->setSubType('slider');
            $consignesetmobile->setEqLogic_id($this->getId());
			$consignesetmobile->setDisplay('generic_type', 'THERMOSTAT_SET_SETPOINT');
            $consignesetmobile->setOrder(74);
			$consignesetmobile->save();
		// cmdAction:  ******************************** //
			$offset = $this->getCmd(null, 'offset');
            if (!is_object($offset)) {
                $offset = new naEnergiecmd();
                $offset->setLogicalId('offset');
                $offset->setIsVisible(1);
                $offset->setName(__('Réglage offset (Piéce)', __FILE__));
            }
            $offset->setType('action');
			$offset->setDisplay('title_disable', 1);
			$offset->setDisplay('message_placeholder', __('Offset', __FILE__));
			$offset->setSubType('message');
            $offset->setEqLogic_id($this->getId());
           	$offset->setOrder(75);
			$offset->save();
		// cmdAction:  ******************************** //
			$writedata = $this->getCmd(null, 'writedata');
            if (!is_object($writedata)) {
                $writedata = new naEnergieCmd();
                $writedata->setLogicalId('writedata');
                $writedata->setIsVisible(0);
                $writedata->setName(__('Write data (cronExt)', __FILE__));
            }
            $writedata->setType('action');
            $writedata->setSubType('other');
			$writedata->setDisplay('generic_type', 'Other');
            $writedata->setEqLogic_id($this->getId());
            $writedata->setOrder(76);
			$writedata->save();
		// cmdAction:  ******************************** //
			$refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new naEnergiecmd();
                $refresh->setLogicalId('refresh');
                $refresh->setIsVisible(1);
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->setOrder(77);
			$refresh->save();
		// cmdAction:  ******************************** //
			$refreshall = $this->getCmd(null, 'refreshall');
            if (!is_object($refreshall)) {
                $refreshall = new naEnergiecmd();
                $refreshall->setLogicalId('refreshall');
                $refreshall->setIsVisible(1);
                $refreshall->setName(__('Rafraichir Tout (Home)', __FILE__));
              	$refreshall->setConfiguration('cmdType', 'home');
            	$refreshall->setConfiguration('homeid', $homeid);
            }
            $refreshall->setType('action');
            $refreshall->setSubType('other');
            $refreshall->setEqLogic_id($this->getId());
            $refreshall->setOrder(78);
			$refreshall->save();
            
            
            
            			
    }
/////////////////////////////////////////	
	public function toHtml($_version = 'dashboard') {
      	if ($this->getConfiguration('eqtuile','') == "core"){
          	self::$_widgetPossibility = array('custom' => 'layout');
          	return eqLogic::toHtml($_version);
        }
      
     
		$replace = $this->preToHtml($_version);
 		if (!is_array($replace)) {
 			return $replace;
  		}
      
      	$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		$_eqType = $this->getConfiguration('type');
		////////////////////// CMD Info /////////////////////
		 
        foreach ($this->getCmd('info') as $cmd) {
			
          	$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				
			
			$replace['#' . $cmd->getLogicalId() . '_collectDate#'] =date('d-m-Y H:i:s',strtotime($cmd->getCollectDate()));
			$replace['#' . $cmd->getLogicalId() . '_updatetime#'] =date('d-m-Y H:i:s',strtotime( $this->getConfiguration('updatetime')));
			
			if ( $cmd->getConfiguration('isdate', false)) {
				$actualdate=date('d/m/Y');
              	$cmdvalue=$cmd->execCmd();
              	$cmdLogId=$cmd->getLogicalId();
              	if (!$cmdvalue) {//date('d/m/Y', strtotime($cmd->execCmd()))
					$htmlvalue = '';
				}elseif ($cmdvalue == 'Nouvel Ordre' ) {//date('d/m/Y', strtotime($cmd->execCmd()))
					$htmlvalue = 'Nouvel Ordre';
				}elseif ($actualdate == date('d/m/Y', strtotime($cmdvalue) )) {//date('d/m/Y', strtotime($cmd->execCmd()))
					$htmlvalue = date('H:i', strtotime($cmdvalue));
				}else {
					$htmlvalue = date('d/m H:i', strtotime($cmdvalue));
				}
              	$replace['#' . $cmdLogId. '#'] = $htmlvalue;
              	//$vcmd=$cmd->execCmd();
              	//log::add('naEnergie', 'debug', ' '.__FUNCTION__ .' cmdvalue: '.$cmdvalue.' to: '.$htmlvalue );
            }
          	if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
          
          	
		}
		    
        $replace['#UpNumberFailed#'] = $this->getConfiguration('UpNumberFailed');
		$replace['#lastCommTherm#'] = date('d-m-Y H:i:s',strtotime( $this->getStatus('lastCommTherm')));
        $replace['#lastCom#'] = date('d-m-Y H:i:s',strtotime( $this->getStatus('lastCommunication') ));
      	$replace['#nahomeid#'] = $this->getConfiguration('HomeId');
      	$replace['#eqLogic_class#'] = 'eqLogic_layout_default';
      
      
      
      	foreach ($this->getCmd('action') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            /*if ($cmd->getLogicalId()=='planningset') {
				$cmd->setConfiguration('listValue',  $this->getCmd(null, 'listplanning')->execCmd());
				$cmd->setValue($this->getCmd(null, 'planning')->execCmd())
                $cmd->save();
				$cmd->refresh();
			}*/
            if ($cmd->getConfiguration('listValue', '') != '') {
				$listOption = '';
				$elements = explode(';', $cmd->getConfiguration('listValue'));
				$foundSelect = false;
				foreach ($elements as $element) {
					list($item_val, $item_text) = explode('|', $element);
					//$coupleArray = explode('|', $element);
					$cmdValue = $cmd->getCmdValue();
					if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
						if ($cmdValue->execCmd() == $item_val) {
							$listOption .= '<option value="' . $item_val . '" selected>' . $item_text . '</option>';
							$foundSelect = true;
						} else {
							$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
						}
					} else {
						$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
					}
				}
				//if (!$foundSelect) {
				//	$listOption = '<option value="">Aucun</option>' . $listOption;
				//}
				
				//$replace['#listValue#'] = $listOption;
				 $replace['#' . $cmd->getLogicalId() . '_id_listValue#'] = $listOption;
				 $replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listOption;
              //log::add('naEnergie','debug',__FUNCTION__.'		***********   listOption '.$listOption);
			}
			
                    
        }
		
		$templateArray[$version] = getTemplate('core', $version, 'eq_'.$_eqType, 'naEnergie');
		
		
		
		return $this->postToHtml($_version, template_replace($replace, $templateArray[$version])); 
   
	}
  
	////////////////////////////////*********************************************////////////////////////////////////////////////
    
    public static function writedataStat($data, $file=null) {
		log::add('naEnergie', 'debug', __FUNCTION__ .' '.$file.' started *****************');
      	$filename = (!$file ? 'data' : $file);
      	$path = __DIR__  . '/../../data';
      	if (!is_dir($path)) {
        	@mkdir($path, 0775, true);
        	log::add('naEnergie', 'debug', 'Dossier data crée...');
		} else {
          	//log::add('naEnergie', 'debug', 'Le dossier data existe');
      		com_shell::execute(system::getCmdSudo() .'chmod 777 -R ' . $path. ' > /dev/null 2>&1;');
          	//log::add('naEnergie', 'debug', 'Droit sudo ok');
     	}
		//file_put_contents($dir . '/' . $_language . '.json', json_encode($_translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		//file_put_contents($path.'/'.$filename.'.json', $data . "\n", FILE_APPEND);
      	file_put_contents($path.'/'.$filename.'.json', $data . "\n");
	}
	
	
	/////////////////////////////////////////
/*
    public function preUpdate() {
		//$new_device = config::byKey('deviceid', 'naEnergie') ;
		//log::add('naEnergie', 'info', 'Fin '.$new_device.__FUNCTION__ .'  ************************');
    }
/////////////////////////////////////////
    public function postUpdate() {
		
		//log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
    }
/////////////////////////////////////////
    public function preRemove() {
    }
/////////////////////////////////////////
    public function postRemove() {
    }
/////////////////////////////////////////
    public function preInsert() {
		//log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
    }
/////////////////////////////////////////
    public function postInsert() {
		//log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
        
    }
/////////////////////////////////////////
    public function preSave() {
		//log::add('naEnergie', 'info', 'Fin '.__FUNCTION__ .'  ************************');
    }
	*/
	
/////////////////////////////////////////	
	
	
	
    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*     * **********************Getteur Setteur*************************** 
    public function toHtml($_version = 'dashboard') {
          $replace = $this->preToHtml($_version);
          if (!is_array($replace)) {
              return $replace;
          }
          
          $version = jeedom::versionAlias($_version);
          if ($this->getDisplay('hideOn' . $version) == 1) {
              return '';
          }
          
          foreach ($this->getCmd() as $cmd) {
              if ($cmd->getType() == 'info') {
                  $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
                  $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
                  $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
                  $replace['#' . $cmd->getLogicalId() . '_collectDate#'] = $cmd->getCollectDate();
                  if ($cmd->getIsHistorized() == 1) {
                      $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
                  }
              } else {
                  $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
              }
          }
          //widget dashboard
          //return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $this->getConfiguration('model'), 'dspsmartplug')));
          $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'H110', 'naEnergie')));
          return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'W215', 'naEnergie')));
                    
      }*/
}
/* * ***************************class naEnergieCmd********************************* */




class naEnergieCmd extends cmd {
   
    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
///////////////////////////////	
	public function dontRemoveCmd() {
      return true;
    }
     */
///////////////////////////////
 
/////////////////////////////////
	public function execute($_options = array()) {
		
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$action= $this->getLogicalId();
		
		log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Action : '.$action.' ');
		
	////////////////////////les actions //////////////////////////////////////////////////
	
		
		if ($action == 'writedata') {
			 $command = naEnergie::cronExt();				
        } 
        elseif ($action == 'refresh') {
			$command = naEnergie::getDataRoom($eqLogic->getLogicalId());
		} 
      	/////
        elseif ($action == 'refreshall') {
			
			//$command =naEnergie::getDataRoom();
			 //$command = naEnergie::getDataRoom();
			 $command = naEnergie::cron15();
			 //naEnergie::getDataRoom();				
            
             
        } 
      	/////
      	elseif ($action == 'setaway' || $action == 'sethg' || $action == 'setmax') {
			$time='';
          	$endtime=null;
			if (isset($_options['message'])){
				$time=$_options['message'];
              	$endtime = time() + ($time* 60);
			}
          	
          	$setmode = substr($action,3);
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(), $setmode, $endtime);
              	
				
		}
      	/////
      	elseif ($action == 'off') {
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),$action);
		} 
		/////
      	elseif ($action == 'schedule') {//Home
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),$action);
			//$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),$action);
			
		}
		/////
      	elseif ($action == 'homeAuto' || $action == 'canceloff'|| $action == 'cancelhg' || $action == 'cancelaway') {//Home
			$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),'schedule');
		}
		/////
      	elseif ($action == 'roomAuto' || $action == 'manoff') {
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(), "roomAuto");
			//log::add('naEnergie','debug','cmd : '.$action. 'non éxécuté'. json_encode($command);
		}
		/////
      	elseif ($action == 'consigneset') {
			$temperatureset = $_options['slider'];
			$time='';
			if (isset($_options['message'])){
				$time=$_options['message'];
			}
			if ($time == '') {
				$eqLogic->changeRoomTherm($eqLogic->getLogicalId(),'manual',$temperatureset);
			} else {
				$endtime = time() + ($time* 60);
				$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),'manual',$temperatureset, $endtime);
			}
		} 
		/////
      	elseif ($action == 'awaymobile' || $action == 'hgmobile' || $action == 'maxmobile' ) {
			if ($action == 'maxmobile') {
				$defaultime = $eqLogic->getConfiguration('spm_duaration');
				if ($defaultime == null || $defaultime == '') {
					$defaultime = 60;
				}
				$endtime = time() + ($defaultime* 60);
				$command =$eqLogic->changeRoomTherm($eqLogic->getLogicalId(),str_replace('mobile','',$action),$endtime);
				
			} else {
				$command =$eqLogic->changeHomeTherm($eqLogic->getLogicalId(),str_replace('mobile','',$action));
			}
		} 
		/////
      	elseif ($action == 'consignemobile') {
			$roomConsset = $_options['slider'];
			$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),'manual',$roomConsset);
		} 
		/////
      	elseif ($action == 'dureeset') {
          	$mode = ( isset($_options['title']) ) ?  $_options['title'] : $eqLogic->getCmd(null, 'modetech')->execCmd();
			$dureeset = $_options['message'];
			$timestamp = strtotime($dureeset);
			//$mode = $eqLogic->getCmd(null, 'modetech')->execCmd();
			switch ($mode){
				case 'away':
					$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),$mode,$timestamp);
				break;
				case 'hg':
					$command = $eqLogic->changeHomeTherm($eqLogic->getLogicalId(),$mode,$timestamp);
				break;
				case 'manual':
					$temperatureset=$eqLogic->getCmd(null, 'consigne')->execCmd();
					$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),'manual',$temperatureset,$timestamp);
					
				break;
				case 'max':
					$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),$mode,$timestamp);
				break;
				default:
                	$command = $eqLogic->changeRoomTherm($eqLogic->getLogicalId(),$mode,null,$timestamp);
					log::add('naEnergie','debug','Vous n\'êtes pas dans un mode pour lequel une durée peut être définie');
				break;
			}
		} 
		/////
      	elseif ($action == 'planningset') {
			$scheduleid = $_options['select'];
          	log::add('naEnergie','debug','cmd : '.$action.': '.$scheduleid);
			$command = $eqLogic->changescheduleTherm($eqLogic->getLogicalId(), $scheduleid);
		}
		/////
      	elseif ($action == 'offset') {
			$offsetval = $_options['message'];
			//$command = $eqLogic->changescheduleTherm($eqLogic->getLogicalId(),$offsetval);
		}
		/////
      	elseif ($action == 'homeboost') {
			$consOrder = $_options['slider'];
          	$endtime=null;
			//$command = $eqLogic->changescheduleTherm($eqLogic->getLogicalId(),$offsetval);
          	$command = $eqLogic->changeThermPoint($eqLogic->getLogicalId(),'manual', $endtime, $consOrder);
		}
		////////Added
		log::add('naEnergie', 'debug', ''.__FUNCTION__ .' Fin cmd-Action : '.$action.' ');
	}

    /*     * **********************Getteur Setteur*************************** */
}


/* * ***************************  class naEnergieGraph  ********************************* */
/*
class  naEnergieGraph extends naEnergie {
	
	public static function cronExt() {	
		 //ori ok
		log::add('naEnergie', 'debug', 'Starting '.__FUNCTION__ .'********');
		$path = dirname(__FILE__) . '/../../data';
		if (!is_dir($path)) {
			 log::add('naEnergie', 'debug', 'data existe pas');
			com_shell::execute(system::getCmdSudo() . 'mkdir ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			com_shell::execute(system::getCmdSudo() . 'chmod 777 -R ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			 log::add('naEnergie', 'debug', 'data crééé');
		} else {
			 log::add('naEnergie', 'debug', 'data existe ');
			com_shell::execute(system::getCmdSudo() . 'chmod 777 -R ' . dirname(__FILE__) . '/../../data' . ' > /dev/null 2>&1;');
			 log::add('naEnergie', 'debug', 'droit sudo');
		}		  
		
		
		$client = self::getClient(__FUNCTION__);
		//$datas = naEnergie::$_data;
	
		$files = array('year','month');
		$scale = array('1month','1day');
		$k=0;
		foreach ($files as $file) {
			$datas = array();
			$eqLogics = eqLogic::byType('naEnergie', true);
			$j = 0;
			foreach ( $eqLogics as $eqLogic) {
				if ($eqLogic->getIsEnable()) {
					for ($i = 0; $i <= 3 ; $i++) {
						$year = date('Y') - $i;
						$begin = new DateTime('first day of January ' . $year);
						$begin = $begin->getTimestamp();
						$end = new DateTime('last day of December ' . $year);
						$end = $end->getTimestamp() + 23*3600;	
						
						$type = $eqLogic->getConfiguration('type');
						$md=$eqLogic->getConfiguration('main_deviceId');
						$datas[$j]['Data_date']=date('d-m-Y H:i:s');
						$datas[$j]['name'] = $eqLogic->getName() ;
						$datas[$j]['type'] = $type;
						switch($type) {
							case 'NAMain'://device WS
								$datatype='Temperature,Humidity,Pressure,Noise,CO2,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measure = $client->_getMeasure($eqLogic->getLogicalId(),NULL, $scale[$k],$datatype , $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getLogicalId() ;
								$datas[$j]['module_id'] = $eqLogic->getLogicalId() ;
								$datas[$j]['datatype'] = $datatype;
								break;
							case 'NAModule1'://module ext WS
								$datatype='Temperature,Humidity,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measure = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), $scale[$k],$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas[$j]['module_id'] = $eqLogic->getLogicalId();
								
								$datas[$j]['datatype'] = $datatype;
								break;
							case 'NAModule4'://Indoor Module sup
								$datatype='Temperature,Humidity,CO2,max_temp,min_temp,max_hum,min_hum,date_max_temp,date_min_temp,date_max_hum,date_min_hum';
								$measure = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), $scale[$k],$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas[$j]['module_id'] = $eqLogic->getLogicalId();
								$datas[$j]['datatype'] = $datatype;
								break;
							case 'NAModule2'://Wind Sensor
								$datatype='WindStrength,WindAngle,GustStrength,GustAngle,date_max_gust';
								$measure = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), $scale[$k],$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas[$j]['module_id'] = $eqLogic->getLogicalId();
								$datas[$j]['datatype'] = $datatype;
								break;
							
							case 'NAModule3'://Rain Gauge
								$datatype='Rain,sum_rain';
								$measure = $client->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), $scale[$k],$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas[$j]['module_id'] = $eqLogic->getLogicalId();
								$datas[$j]['datatype'] = $datatype;
								break;
							case 'NATherm1'://module TH
								$clientTh = self::getClient(__FUNCTION__,'NATherm1');
								$datatype='Temperature,Sp_Temperature,BoilerOn,BoilerOff,date_max_temp,max_temp,min_temp,date_min_temp';
								$measure = $clientTh->_getMeasure($eqLogic->getConfiguration('main_deviceId'), $eqLogic->getLogicalId(), $scale[$k],$datatype, $begin, $end, 1024, FALSE, FALSE);
								$datas[$j]['device_id'] = $eqLogic->getConfiguration('main_deviceId');
								$datas[$j]['module_id'] = $eqLogic->getLogicalId();
								$datas[$j]['datatype'] = $datatype;
								break;	
						} //Fin switch($type)
						
							if ($measure != null) {
								$datas[$j]['year'][$year] = $measure;
							} else {
								break;
							}
							//log::add('naEnergie','debug', 'cron_datats '.$type.'|'.json_encode($datas));
							
							
					}//Fin for
					$j++;	
				
				}//Fin if ($eqLogic->getIsEnable()) {
			}//Fin foreach ( $eqLogics as $eqLogic)
			
			file_put_contents(dirname(__FILE__) . '/../../data/' . $file .'.json', json_encode($datas));	
			$k++;
			}// fin foreach ($files as $file)		
			log::add('naEnergie', 'debug', 'Fin '.__FUNCTION__ .'********');
		 		  
      }
///////////////////////////////	 
	
	////////////////////////////////					
	public function getDataGraph($device_id, $module_id, $scale, $type, $date_begin, $date_end, $limit, $subtitle, $real_time) {
		log::add('naEnergie', 'debug', '******** Starting '.__FUNCTION__ .'********');
		$client = self::getClient("getDataGr");
		$datas = naEnergie::$_data;
		
		if ($module_id == 0) {
			$module_id = NULL;
		}
		log::add('naEnergie','debug', 'f getDataGraph scale: '.$scale);
		log::add('naEnergie','debug', 'f getDataGraph type: '.$type);
		log::add('naEnergie','debug', 'f getDataGraph date_begin: '.$date_begin);
		log::add('naEnergie','debug', 'f getDataGraph date_end: '.$date_end);
		log::add('naEnergie','debug', 'f getDataGraph limit: '.$limit);
		//$device_id.','.$module_id.','.$scale.','.$type.','.$date_begin.','.$date_end.','.$limit.','.'FALSE'.','.'FALSE'
		
		
		$data_module = array();
		$data_module['type_graph'] = $subtitle;
		
		//type device
		$device = eqLogic::byLogicalId($device_id,'naEnergie');
		if (is_object($device) && $device_id===$module_id || $module_id===NULL){
			
			$devicetype = $device->getConfiguration('type');
			$measure = $client->_getMeasure($device_id, $module_id, $scale, $type, $date_begin, $date_end, $limit, FALSE, FALSE);
			log::add('naEnergie','debug', 'f getDataGraph measure device: '.$device_id.'|'.$module_id .': '. json_encode($measure, true));
		
			$data_module['name_module'] = $device->getName();
			$data_module['type']=$device->getConfiguration('type');
			$data_module['infos'] = json_encode($measure); 
			//log::add('naEnergie','debug', 'f getDataGraph device type: '.$device->getConfiguration('type'));
				//$eqLogic->setConfiguration('mesures',$statmodule['dashboard_data']);
			$data_module['temperature_module'] = $device->getConfiguration('dash_data')['Temperature'];
			$data_module['Humidity_module'] = $device->getConfiguration('dash_data')['Humidity'];		
			$data_module['Pressure_module'] = $device->getConfiguration('dash_data')['Pressure'];		
			$data_module['Noise_module'] = $device->getConfiguration('dash_data')['Noise'];		
			$data_module['CO2_module'] = $device->getConfiguration('dash_data')['CO2'];
			$data_module['max_temp_module'] = $device->getConfiguration('dash_data')['max_temp'];	
			$data_module['min_temp_module'] = $device->getConfiguration('dash_data')['min_temp'];
			$data_module['wifi_status'] = self::getWifi($device->getConfiguration('wifi_status'));
		}//type module					
		elseif (is_object(eqLogic::byLogicalId($module_id,'naEnergie'))){ 
			
			$module = eqLogic::byLogicalId($module_id,'naEnergie');
			$moduletype = $module->getConfiguration('type');
			log::add('naEnergie','debug', 'f getDataGraph module type: '.$module->getConfiguration('type'));
			$measure = $client->_getMeasure($device_id, $module_id, $scale, $type, $date_begin, $date_end, $limit, FALSE, FALSE);
			log::add('naEnergie','debug', 'f getDataGraph measure: '.$device_id.'|'.$module_id .': '. json_encode($measure, true));
			
			$data_module['name_module'] = $module->getName();
			$data_module['type'] = $module->getConfiguration('type');
			$data_module['infos'] = json_encode($measure); 
			
			
			switch($moduletype)
			{	//Indoor device meteo
				
				case "NAMain":
					log::add('naEnergie','debug', 'f getDataGraph module: '.$module->getConfiguration('type'));
				
					$data_module['temperature_module'] = $module->getConfiguration('dash_data')['Temperature'];
					$data_module['Humidity_module'] = $module->getConfiguration('dash_data')['Humidity'];		
					$data_module['Pressure_module'] =$module->getConfiguration('dash_data')['Pressure'];		
					$data_module['Noise_module'] = $module->getConfiguration('dash_data')['Noise'];		
					$data_module['CO2_module'] = $module->getConfiguration('dash_data')['CO2'];	
					$data_module['max_temp_module'] = $module->getConfiguration('dash_data')['max_temp'];	
					$data_module['min_temp_module'] = $module->getConfiguration('dash_data')['min_temp'];
					$data_module['wifi_status'] = self::getWifi($module->getConfiguration('wifi_status'));
				break;
				// Outdoor Module
				
				case "NAModule1": 
					//log::add('naEnergie','debug', 'f getDataGraph module: '.$module->getConfiguration('type'));
					$data_module['temperature_module'] = $module->getConfiguration('dash_data')['Temperature'];
					$data_module['Humidity_module'] = $module->getConfiguration('dash_data')['Humidity'];
					$data_module['max_temp_module'] = $module->getConfiguration('dash_data')['max_temp'];	
					$data_module['min_temp_module'] = $module->getConfiguration('dash_data')['min_temp'];
					$data_module['battery_percent'] = self::getBattery($module->getConfiguration('type'),$module->getConfiguration('battery_percent'));
				;break;
				
				//Wind Sensor : WindStrength,WindAngle,Guststrength,GustAngle,WindHistoric
				case "NAModule2": 
					$data_module['WindStrength_module'] = $module->getConfiguration('dash_data')['WindStrength'];	
					$data_module['WindAngle_module'] = $module->getConfiguration('dash_data')['WindAngle'];	
					$data_module['Guststrength_module'] = $module->getConfiguration('dash_data')['Guststrength'];
					$data_module['GustAngle_module'] = $module->getConfiguration('dash_data')['GustAngle'];
					$data_module['WindHistoric_module'] = $module->getConfiguration('dash_data')['WindHistoric'];
					$data_module['battery_percent'] = self::getBattery($module->getConfiguration('type'),$module->getConfiguration('battery_percent'));
					;break;
		
				//Rain Gauge Rain,sum_rain_1,sum_rain_24
				case "NAModule3": 
					//log::add('naEnergie','debug', 'f getDataGraph module: '.$module->getConfiguration('type'));
					$data_module['Rain_module'] = $module->getConfiguration('dash_data')['Rain'];
					$data_module['sum_rain_1_module'] = $module->getConfiguration('dash_data')['sum_rain_1'];
					$data_module['sum_rain_24_module'] = $module->getConfiguration('dash_data')['sum_rain_24'];
					$data_module['battery_percent'] = self::getBattery($module->getConfiguration('type'),$module->getConfiguration('battery_percent'));
					
					break;
				
				//Indoor Module
				case "NAModule4": 
					//log::add('naEnergie','debug', 'f getDataGraph module: '.$module->getConfiguration('type'));
					$data_module['temperature_module'] = $module->getConfiguration('dash_data')['Temperature'];
					$data_module['Humidity_module'] = $module->getConfiguration('dash_data')['Humidity'];		
					//$data_module['Pressure_module'] = $module->getConfiguration('Pressure'];		
					//$data_module['Noise_module'] = $module->getConfiguration('Noise');		
					$data_module['CO2_module'] = $module->getConfiguration('dash_data')['CO2'];	
					$data_module['max_temp_module'] = $module->getConfiguration('dash_data')['max_temp'];	
					$data_module['min_temp_module'] = $module->getConfiguration('dash_data')['min_temp'];
					$data_module['wifi_status'] = self::getWifi($module->getConfiguration('wifi_status'));
					$data_module['battery_percent'] = self::getBattery($module->getConfiguration('type'),$module->getConfiguration('battery_percent'));
						
					break;
				//Indoor Module
				case "NATherm1": 
					//log::add('naEnergie','debug', 'f getDataGraph module: '.$module->getConfiguration('type'));
					$data_module['temperature_module'] = $module->getConfiguration('dash_data')['Temperature'];
					$data_module['cons_module'] = $module->getConfiguration('dash_data')['Sp_Temperature'];	
					$data_module['boilerOn'] = $module->getConfiguration('dash_data')['BoilerOn'];
					$data_module['boilerOff'] = $module->getConfiguration('dash_data')['BoilerOff'];
					
					$data_module['min_temp_module'] = $module->getConfiguration('dash_data')['min_temp'];
					$data_module['max_temp_module'] = $module->getConfiguration('dash_data')['max_temp'];
					$data_module['sum_boiler_on'] = $module->getConfiguration('dash_data')['sum_boiler_on'];
					$data_module['sum_boiler_off'] = $module->getConfiguration('dash_data')['sum_boiler_off'];
					
					$data_module['rf_status'] = $module->getConfiguration('rf_status');
					$data_module['battery_percent'] = self::getBattery($module->getConfiguration('type'),$module->getConfiguration('battery_percent'));
					$data_module['wifi_status'] = self::getWifi($module->getConfiguration('wifi_status'));
					break;
				
				//Indoor Module
				case "NAPlug": 
					return $type = 'module_rel';
					break;
			}	
			
			
			
			
			
			$data_module['rf_status'] = self::getSignal($module->getConfiguration('rf_status'));
			//if (array_key_exists('wifi_status', $data_module)) {
				//log::add('naEnergie','debug', 'conf: ' . $module->getConfiguration('rf_status'));
			//}
			
		} 
		
		
		log::add('naEnergie','debug', 'f getDataGraph data_module: '.json_encode($data_module));
		log::add('naEnergie', 'debug', 'Fin '.__FUNCTION__ .'********');
		return($data_module);	
	}

	///////////////////////////////		
	public static function treeById($_eqType_name) {
        $values = array(
            'eqType_name' => $_eqType_name
        );
        $sql = 'SELECT *
                FROM eqLogic
                WHERE eqType_name=:eqType_name
				';
        $results = DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL);
        $return = array();
        foreach ($results as $result) {
            $return[] = eqLogic::byId($result['id']);
        }
        return $return;
    }
}//Fin class  naEnergieGraph extends naEnergie {
*/

?>