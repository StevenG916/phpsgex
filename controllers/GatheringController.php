<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\City;
use phpsgex\framework\models\units\Troop;
use phpsgex\framework\models\units\UnitAction;
use phpsgex\framework\models\units\UnitData;
use phpsgex\framework\models\research\ResearchVillage;

class GatheringController extends BaseController {
	/** @var \phpsgex\framework\models\City $city */
	public $city;
	
	public function Details(){
        if( isset($_GET["village"]) ){
			$vill_id = $_GET["village"];
			global $DB;
			$my_id = $this->user->GetCurrentCity()->id;
                        
			$this->viewData["city"] = $this->user->GetCurrentCity();

			$qr= $DB->query("select * from ".TB_PREFIX."units where `id`= {$vill_id} and `from` = {$my_id} and `owner_id` = {$my_id}  ");
			if($qr->num_rows != 0){
				$row= $qr->fetch_array();
				$this->id = $row['id'];
				$this->ownerid = $row['owner_id'];
				$this->id_unt = $row['id_unt'];
				$this->uqnt = $row['uqnt'];
				$this->to = $row['to'];
				$this->from = $row['from'];
				$this->time = $row['time'];
				$this->startTime = $row['startTime'];

				switch($row['action']){
					case UnitAction::Attack:
						$this->action = "Attacco a ";
						break;
					case UnitAction::Re_entry:
						$this->action = "Support a ";
						break;
				}
				
				$this->viewData["beforeDot"] = explode(".", ($this->time) );
				
				if(date("d-m-Y",$this->viewData["beforeDot"][0]) == date("d-m-Y")){
					$this->viewData["today"] = "Oggi alle ".date("H:i:s:ms",$this->viewData["beforeDot"][0]);
				}else{
					$this->viewData["today"] = date("d-m-Y H:i:s:ms", $this->viewData["beforeDot"][0]);
				}
			
				$this->viewData["day"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 0, 2);
				$this->viewData["month"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 3, 2);
				$this->viewData["year"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 6,4);
			
				$this->viewData["hour"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 11, 2);
				$this->viewData["minutes"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 14, 2);
				$this->viewData["seconds"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 17, 2);

				$this->viewData["arr_tot"] = [];
				$this->viewData["unit_arr"] = [];
				
				$qr= $DB->query("select * from ".TB_PREFIX."units where `from`= {$this->viewData["city"]->id} and `to`= {$this->to}");
				while($row= $qr->fetch_array()){
					$carry = UnitData::Instantiate($row["id_unt"])->carry;
					$this->viewData["unit_arr"][$row['id_unt']] = $row['uqnt'];
					$cap_tot = ($carry * $row['uqnt']);
					$this->viewData["arr_tot"][] = $cap_tot;
				} 

			}else{
				header("Location: ?pg=Gathering");
			}

			parent::Index("detailsRaid");
		}
    }

    public function Attack(){ 
		if( empty($_POST) || !isset($_POST["village"]) || !isset($_POST['act']) || multi_submit() )
			header("Location: ?pg=Gathering");
	
		$cu= $this->user;
		$atkCityId= (int)$_POST["village"];

		global $DB;

        $minSpeed= 99;
        foreach( $_POST["unit"] as $troopId => $num ){
            if( $num <= 0 ) continue;
            $troop= Troop::Instantiate($troopId);
            $unt= UnitData::Instantiate($troop->unit->id);
            $minSpeed= min($unt->speed, $minSpeed);
        }
        
        if($minSpeed==0) $minSpeed= 1;

        $otherCity= \phpsgex\framework\models\City::Instantiate($atkCityId);
        $arriveTime= time() + $this->user->GetCurrentCity()->mapPosition->GetDistance($otherCity->mapPosition) *99/$minSpeed;

		foreach( $_POST["unit"] as $troopId => $num ){
			try {
                $troop= Troop::Instantiate($troopId);
            } catch(\Exception $ex){ continue; }
            $num= min( $troop->quantity, $num );
            if($num <= 0) continue;

            $DB->query("update ".TB_PREFIX."units
                set uqnt= uqnt -$num
                where id= $troopId");

			if($_POST["act"] == UnitAction::Attack){
				$action = UnitAction::Attack;
			}else if($_POST["act"] == UnitAction::Support){
				$action = UnitAction::Support;
			}else{
				header("Location: ?pg=Gathering");
			}
            
			$DB->query("insert into ".TB_PREFIX."units values
						(null, {$troop->unit->id}, $num, {$cu->id}, {$cu->GetCurrentCity()->id}, $atkCityId, null, ".time().",$arriveTime, $action)");
		
		}

		header("Location: ?pg=Gathering");
		 
	}


	public function Confirm(){
		if( empty($_POST) || !isset($_POST["village"]) || !isset($_POST['act']))
			header("Location: ?pg=Gathering");

		$atkCityId= (int)$_POST["village"];
		$this->arr_tot = [];

		$minSpeed= 99;
        foreach( $_POST["unit"] as $troopId => $num ){
            if( $num <= 0 ) continue;
            $troop= Troop::Instantiate($troopId);
            $unt= UnitData::Instantiate($troop->unit->id);
			$minSpeed= min($unt->speed, $minSpeed);
			$cap_car = $unt->carry;
			$cap_tot = ($cap_car * $num);
			$this->arr_tot[] = $cap_tot;
		}

		if($minSpeed==0) $minSpeed= 1;

        $this->city= \phpsgex\framework\models\City::Instantiate($atkCityId);
        $arriveTime= time() + $this->user->GetCurrentCity()->mapPosition->GetDistance($this->city->mapPosition) *99/$minSpeed;

		$this->viewData["beforeDot"] = explode(".", ($arriveTime) );


		if(date("d-m-Y",$this->beforeDot[0]) == date("d-m-Y")){
			$this->viewData["today"] = "Oggi alle ".date("H:i:s:ms",$this->viewData["beforeDot"][0]);
		}else{
			$this->viewData["today"] = date("d-m-Y H:i:s:ms", $this->viewData["beforeDot"][0]);
		}
	
		$this->viewData["day"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 0, 2);
		$this->viewData["month"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 3, 2);
		$this->viewData["year"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 6,4);
	
		$this->viewData["hour"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 11, 2);
		$this->viewData["minutes"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 14, 2);
		$this->viewData["seconds"] = substr(date("d-m-Y H:i:s",$this->viewData["beforeDot"][0]), 17, 2);


		parent::Index("attackConfirm");
		
	}

	
	public function GetCurrentTime($sec){
        $cur_time = time();
        $time_elapsed = $sec - $cur_time;

        $hours = 3600;
        $min = 60;
        $number_hour = 0;
        while($hours <= $time_elapsed){
             $time_elapsed -= $hours;
              ++$number_hour;
        }
        $number_min = 0;
        while($min <= $time_elapsed){
             $time_elapsed -= $min;
             ++$number_min;
        }
        return sprintf("%01s", $number_hour).":".sprintf("%02s", $number_min).":".sprintf("%02s", $time_elapsed);
    }


    public function Index(){
		$this->viewData["city"] = $user->GetCurrentCity();

		$ret_atk= array();
		$ret_sup = array();

		global $DB;
		try{ 
			$unit_out = $DB->query("select * from ".TB_PREFIX."units where `from`= ".$this->viewData["city"]->id." and time is not null group by `time`, `to`, `action` order by `time` asc");
		}catch(Exception $e){ error_log($e); }
		try{
			$unit_in = $DB->query("select * from ".TB_PREFIX."units where `to`= ".$this->viewData["city"]->id." and time is not null group by `time`, `to`, `action` order by `time` asc");
		}catch(Exception $e){ error_log($e); }
		
		$this->viewData["unit_out_num_rows"] = $unit_out->num_rows;
		
		while($row = $unit_out->fetch_array()){
			$ret_atk[] = $row;
		}
		$this->viewData["unit_out_fetch"] = $ret_atk;

		$this->viewData["unit_in_num_rows"] = $unit_in->num_rows;

		while($row = $unit_in->fetch_array()){
			$ret_sup[] = $row;
		}
		
		$this->viewData["unit_in_fetch"] = $ret_sup;

		
		parent::Index("gathering");
		
	}
	

	public function ResearchVillage(){
		if(isset($_POST["submit_search"])) {
			ResearchVillage::search__($_POST);
		}
	}
	
	public function withdraw_troops(){
		if( empty($_POST) || !isset($_POST["id"]) || !isset($_POST["act"]) || multi_submit() ){ }

		$city = $user->GetCurrentCity();

		$id_ = $_POST['id'];
		global $DB;

		$qr= $DB->query("select * from ".TB_PREFIX."units where `id`={$id_} and `action`= ".UnitAction::Support);

		if($qr->num_rows != 0) {
			$row= $qr->fetch_array();

			$minSpeed= 99;
			$unt= UnitData::Instantiate($row['id_unt']);
			$minSpeed= min($unt->speed, $minSpeed);
			
			if($minSpeed==0) $minSpeed= 1;

			if($_POST['act'] == "get"){ $v = $row['to']; }else{ $v = $row['from']; }

			$otherCity= \phpsgex\framework\models\City::Instantiate($v);
			$end_time= time() + $this->user->GetCurrentCity()->mapPosition->GetDistance($otherCity->mapPosition) *99/$minSpeed;

			$DB->query("update ".TB_PREFIX."units 
						set `from`= {$row['to']}, `to`= {$row['from']}, `where`= null,
						action= ".UnitAction::Re_entry.", `time`= $end_time 
						where id= ".$row['id']);

			echo json_encode("__SUCCESS__");
		}

		
	}

	public function atk_remove(){
		if( empty($_POST) || !isset($_POST["id"]) || !isset($_POST['atk_remove']) || multi_submit() ){ }
		
		$id_ = $_POST['id'];
		$from_ = $_POST['from'];
		$to_ = $_POST['to'];
		$time_ = $_POST['time'];
		$own_ = $this->user->id;
		$j_s;
		global $DB;

		$arr_trp_ = [];
		$act = null;
		$qr= $DB->query("select * from ".TB_PREFIX."units where `from`= {$from_} and `time`= {$time_} and `to`= {$to_}");
		while($row= $qr->fetch_array()){
			$unit= new Troop(0, $row);
			
			$dest= City::Instantiate($to_);
			
			$cancel_sec = 600;			

			$time = time();
			$start_time = $time;

			$end_time = $time + ($time - $row['startTime']);

			if (!(($row['startTime'] + $cancel_sec) <= time())) { 
				$DB->query("update ".TB_PREFIX."units 
							set `from`= {$to_}, `to`= {$from_}, 
							action= ".UnitAction::Re_entry.", `time`= $end_time 
							where id= ".$row['id']);

				$j_s = "__SUCCESS__";
			}else{
				$j_s = "__ERROR__";
			}
		} 

		echo json_encode($j_s);
	}


	public function DeploymentTroops(){
		$city = $user->GetCurrentCity();

		$nmtp_row= array();
		$nmtp_row_2= array();

		global $DB;
		
		try{ 
			$nmtp = $DB->query("select * from ".TB_PREFIX."units where `owner_id`= {$user->id} and `from`={$city->id} and `time` is null and `action`=2 order by `startTime` asc");
		}catch(Exception $e){ error_log($e); }

		try{
			$nmtp2 = $DB->query("select * from ".TB_PREFIX."units where `where`= {$city->id} and `time` is null and `action`=2 order by `startTime` asc");
		}catch(Exception $e){ error_log($e); }
		
		while($row = $nmtp->fetch_array()){
			$nmtp_row[] = $row;
		}
		$this->viewData["nmtp_row"] = $nmtp_row;

		while($row2 = $nmtp2->fetch_array()){
			$nmtp_row_2[] = $row2;
		}
		$this->viewData["nmtp_row_2"] = $nmtp_row_2;


		parent::Index("DeploymentTroops");
	}

	protected function calc_start_time($arr_trp_, $to){
		$minSpeed= 99;
		foreach( $arr_trp_ as $troopId => $num ){
			if( $num <= 0 ) continue;
			$troop= Troop::Instantiate($troopId);
			$unt= UnitData::Instantiate($troop->unit->id);
			$minSpeed= min($unt->speed, $minSpeed);
		}
	 
		if($minSpeed==0) $minSpeed= 1;

		$otherCity= \phpsgex\framework\models\City::Instantiate($to);
		$arriveTime= $this->user->GetCurrentCity()->mapPosition->GetDistance($otherCity->mapPosition) *99/$minSpeed;
		return $arriveTime;
	}
	
}