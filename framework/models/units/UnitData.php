<?php
namespace phpsgex\framework\models\units;

use phpsgex\framework\models\buildings\BuildingRequirement;
use phpsgex\framework\models\research\ResearchRequirement;

class UnitData {
	public $id, $speed, $attack, $defence, $health, $carry, $name, $raceId, $description, $image, $trainTime, $build, $type;

	public static $_instantiate;
	public static function Instantiate($id, Array $row= null){
        return call_user_func(self::$_instantiate, $id, $row);
    }

	public function __construct( $_id, Array $row= null ){
		global $DB;
        if($row == null) {
            $this->id= $_id;
            $qr= $DB->query("SELECT * FROM ".TB_PREFIX."t_unt WHERE id= $_id");

            if($qr->num_rows ==0) throw new \Exception("Invalid unit id $_id");

            $row= $qr->fetch_array();
        } else $this->id= $row["id"];

        $this->speed= (int)$row['vel'];
		$this->attack= (int)$row['atk'];
		$this->defence= (int)$row['def'];
		$this->health= (int)$row['health'];
        $this->trainTime= (int)$row['etime'];
        $this->name= $row['name'];
        $this->description= $row['desc'];
        $this->image= $row['img'];
        $this->raceId= (int)$row["race"];
        $this->carry= (int)$row["res_car_cap"];
        $this->build= $row["build"];
        $this->type= $row["type"];
	}

    public function GetTrainCosts( $_quantity = 1 ){
        if( $_quantity < 1 ) throw new \Exception("quantity < 1");

        global $DB;
        $costs = Array();
        $resd= $DB->query("SELECT * FROM `".TB_PREFIX."resdata`");
        while( $row= $resd->fetch_array() ){
            $qbudcost= $DB->query("SELECT * FROM `".TB_PREFIX."t_unt_resourcecost` WHERE `unit`= {$this->id} AND `resource` =".$row['id']);
            if( $qbudcost->num_rows >0 ){
                $acst= $qbudcost->fetch_array();
                $costs[ $row['id'] ]= $_quantity *$acst['cost'];
            } else $costs[ $row['id'] ]= 0;
        }

        return $costs;
    }

    public function GetBuildingsRequirements(){
        global $DB;
        $qr= $DB->query("SELECT * FROM `".TB_PREFIX."t_unt_reqbuild` WHERE `unit`= ".$this->id);

        $requisites = Array();
        if( $qr->num_rows ==0 ) return $requisites; //no requisites!

        while( $row = $qr->fetch_array() ){
            $requisites[] = new BuildingRequirement( $row['reqbuild'], $row['lev'] );
        }
        return $requisites;
    }

    public function GetResearchRequirements(){
        global $DB;
        $qr= $DB->query("SELECT * FROM `".TB_PREFIX."t_unt_req_research` WHERE `unit`= ".$this->id);

        $requisites= Array();
        if( $qr->num_rows ==0 ) return $requisites; //no requisites!

        while( $row= $qr->fetch_array() ){
            $requisites[] = new ResearchRequirement( $row['reqresearch'], $row['lev'] );
        }
        return $requisites;
    }

    public static function GetAll($raceId= null){
        global $DB;
        $ret= Array();

        $qr= $DB->query("SELECT * FROM ".TB_PREFIX."t_unt ".( $raceId != null ? "where race is null or race= $raceId" : "" ) );
        while( $row= $qr->fetch_array() )
            $ret[]= self::Instantiate( 0, $row );

        return $ret;
    }
}

UnitData::$_instantiate= function($id, Array $row= null){ return new UnitData($id, $row); };