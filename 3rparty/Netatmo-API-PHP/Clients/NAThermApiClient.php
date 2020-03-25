<?php

namespace Netatmo\Clients;

/**
 * NETATMO THERMOSTAT API PHP CLIENT (Limad44 Mode)
 *
 * For more details upon NETATMO API, please check https://dev.netatmo.com/doc
 * @author Limad44 For Jeedom use
 */
class NAThermApiClient extends NAApiClient
{	
	/******************************************************/
	/***********								***********/
	/*			New API integration by Limad44			  */
	/***********								***********/
	/******************************************************/
	
	//**GET/homesdata,homestatus,roommeasure
	
	
	/***** homesdata: Retrieve user's homes and their topology.****/
	public function _getHomesdata($home_id = NULL, $gateway_types= NULL){
        $params = array();
        if(!is_null($home_id)) $params['home_id'] = $home_id ;
		if(!is_null($gateway_types)) $params['gateway_types'] = $gateway_types ;
        return $this->api('homesdata', 'GET', $params);
    }
	/***** homestatus: Get the current status and data measured for all home devices.****/
	public function _getHomestatus($home_id, $gateway_types= NULL){
        $params = array('home_id' => $home_id);
       
		if(!is_null($gateway_types)) $params['gateway_types'] = $gateway_types ;
        return $this->api('homestatus', 'GET', $params);
    }
	
	/***** roommeasure: Retrieve data from a Room****/
	public function _getMeasure($home_id, $room_id= NULL, $scale, $type, $start = NULL, $end = NULL, $limit = NULL, $optimize = NULL, $realtime = NULL){
        $params = array('home_id' => $home_id,
						'scale' => $scale,
                        'type' => $type);

        $optionals = array('room_id' => $room_id,
                           'date_begin' => $start,
                           'date_end' => $end,
                           'limit' => $limit,
                           'optimize' => $optimize,
                           'real_time' => $realtime);
        foreach($optionals as $key => $value)
        {
            if(!is_null($value)) $params[$key] = $value;
        }
        
        
       return $this->api('getmeasure', 'GET', $params);
    }
   
	
	/***** roommeasure: Retrieve data from a Room****/
	public function _getRoomMeasure($home_id, $room_id, $scale, $type, $start = NULL, $end = NULL, $limit = NULL, $optimize = NULL, $realtime = NULL){
        $params = array('home_id' => $home_id,
						'room_id' => $room_id,
                        'scale' => $scale,
                        'type' => $type);

        $optionals = array('date_begin' => $start,
                           'date_end' => $end,
                           'limit' => $limit,
                           'optimize' => $optimize,
                           'real_time' => $realtime);
        foreach($optionals as $key => $value)
        {
            if(!is_null($value)) $params[$key] = $value;
        }
        
        
       return $this->api('getroommeasure', 'GET', $params);
    }
   
	//Actions
    /***** setroomthermmode: Set the home heating system to use schedule/ away/ frost guard mode****/
   
  /***** setthermpoint: Set the home heating system to use schedule/ away/ frost guard mode (schedule = Schedule of the house; away= away from the house; hg= frostguard for long departure)****/
	public function _setThermPoint($home_id, $room_id, $mode, $temp= NULL, $endtime = NULL){
        $params = array(
						'home_id' => $home_id,
						'room_id' => $room_id,
						'mode' => $mode//home
						);
		if(!is_null($temp)) $params['temp'] = $temp ;
		if(!is_null($endtime)) $params['endtime'] = $endtime ;
		return $this->api('setthermpoint', 'POST', $params);
    }
  /***** setroomthermmode: Set the home heating system to use schedule/ away/ frost guard mode****/
    public function _setThermMode($home_id, $mode,$endtime = NULL, $schedule_id = NULL){
        $params = array(
						'home_id' => $home_id,
						'mode' => $mode
						);
		if(!is_null($endtime)) $params['endtime'] = $endtime ;
      	if(!is_null($schedule_id)) $params['schedule_id'] = $schedule_id ;
		return $this->api('setthermmode', 'POST', $params);
      
    }
	
	
	/***** Setroomthermpoint: Set a manual temperature to a room. or switch back to home mode****/
	public function _setRoomThermPoint($home_id, $room_id, $mode, $temp= NULL, $endtime = NULL){
        $params = array(
						'home_id' => $home_id,
						'room_id' => $room_id,
						'mode' => $mode
						);
		if(!is_null($temp)) $params['temp'] = $temp ;
		if(!is_null($endtime)) $params['endtime'] = $endtime ;
		return $this->api('setroomthermpoint', 'POST', $params);
    }
	
	/***** switchhomeschedule: Apply a specific schedule****/
	public function _switchSchedule($home_id, $schedule_id){
        $params = array(
						'home_id' => $home_id,
						'schedule_id' => $schedule_id
						);
		
		return $this->api('switchhomeschedule', 'POST', $params);
    }
    
    
    /***** Synchomeschedule: Modify the given schedule for the home. If it's the current schedule, it sends the modification to the devices.****/
	public function _syncHomeSchedule($home_id, $schedule_id, $zones, $timetable, $hg_temp, $away_temp, $name=NULL)
    {
        $params = array(
						'home_id' => $home_id,
						'schedule_id' => $schedule_id,
						'zones' => $zones,
						'timetable' => $timetable,
						'hg_temp' => $hg_temp,
						'away_temp' => $away_temp
						);
		if(!is_null($name)) $params['name'] = $name;
		return $this->api('synchomeschedule', 'POST', $params);
    }
	
	/***** Renamehomeschedule: Update the given schedule name.****/
	public function _renameHomeSchedule($home_id, $schedule_id, $name){
        $params = array(
						'home_id' => $home_id,
						'schedule_id' => $schedule_id,
						'name' => $name
						);
		return $this->api('renamehomeschedule', 'POST', $params);
    }
	
	
	/***** Deletehomeschedule: Delete the given schedule.****/
	public function _deleteHomeSchedule($home_id, $schedule_id){
        $params = array(
						'home_id' => $home_id,
						'schedule_id' => $schedule_id
						);
		return $this->api('deletehomeschedule', 'POST', $params);
    }
	
	/***** Createnewhomeschedule: Update the given schedule name.****/
	public function _createNewHomeSchedule($home_id, $timetable, $zones, $name, $hg_temp, $away_temp){
        $params = array(
						'home_id' => $home_id,
						'timetable' => $timetable,
						'zones' => $zones,
						'name' => $name,
						'hg_temp' => $hg_temp,
						'away_temp' => $away_temp
						);
		
		return $this->api('createnewhomeschedule', 'POST', $params);
	}
	
	
	
	public function _getThermReport($device_id, $month= NULL, $year = NULL){
        $params = array('device_id' => $device_id,
						'month' => $scale,
                        'year' => $type);

        
        
        
       return $this->api('getthermreport', 'GET', $params);
    }
	
	
	
	////// Fin Api inculde Valves/////////////////////////////
}
?>