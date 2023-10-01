<?php
namespace phpsgex\framework\models\buildings;

//this class represents t_builds, it's not a builded building in a city
class BuildingData {
	public $id, $raceId, $name, $function, $produceResourceId, $image, $description, $buildTime, $timeMultiplier,
			$gainPoints, $pointLevelMul, $maxLevel, $imgTags, $optHtml;

	public static function GetAll($raceId= null, $function= null){
		global $DB;
		$qr= $DB->query("select * from ".TB_PREFIX."t_builds where 1".
				( $raceId != null ? " and arac is null or arac= $raceId" : "" ).
				( $function != null ? " and func= '$function'" : "" )
		);
		$ret= Array();

		while($row= $qr->fetch_array())
			$ret[]= new BuildingData(null,$row);

		return $ret;
	}

	public function __construct( $_id= null, Array $row= null ){
		global $DB;

		if($row==null) {
			$query = $DB->query("select * from " . TB_PREFIX . "t_builds where id =" . (int)$_id);
			if ($query->num_rows == 0) throw new \Exception("Invalid build id " . $_id);
			$building = $query->fetch_array();
		} else $building= $row;
		
		$this->id = (int)$building["id"];
		$this->raceId = $building["arac"];
		$this->name = $building["name"];
		$this->function = $building["func"];
		$this->produceResourceId = $building["produceres"];
		$this->image = $building["img"];
		$this->description = $building["desc"];
		$this->buildTime = $building["time"];
        $this->timeMultiplier= $building["time_mpl"];
		$this->gainPoints= $building["gpoints"];
		$this->pointLevelMul= $building["pointmul"];
        $this->maxLevel = $building['maxlev'];
		$this->imgTags= $building["imgtags"];
		$this->optHtml= $building["opthtml"];
	}
	
	//returns a indexed list (by resource id) with cost foreach resouce, this list will be long as the number of resources in resdata
	public function GetBuildCosts( $_level ){
		if( $_level <= 0 ) throw new \Exception("Level <= 0");
		
		global $DB;
		$ret= Array();
		foreach( BuildResourceCost::GetCosts($this->id) as $cost ){
                    $ret[$cost->resource]= (int)($cost->cost * $cost->moltiplier * $_level);
                }
                
		return $ret;
	}

    /**
     * @return array
     */
	public function GetBuildingsRequirements(){
		global $DB;
		$qr= $DB->query("SELECT * FROM `".TB_PREFIX."t_build_reqbuild` WHERE `build` = ".$this->id);
		
		$requisites = Array();
		if( $qr->num_rows ==0 ) return $requisites; //no requisites!

		while( $row = $qr->fetch_array() ){
            $requisites[] = new BuildingRequirement( $row['reqbuild'], $row['lev'] );
		}
		return $requisites;
	}

	public function GetResearchRequirements(){
		global $DB;
		$qr= $DB->query("SELECT * FROM `".TB_PREFIX."t_build_req_research` WHERE `build`= ".$this->id);
		
		$requisites= Array();
		if( $qr->num_rows ==0 ) return $requisites; //no requisites!

		while( $row= $qr->fetch_array() ){
            $requisites[]= new ResearchRequirement( $row['reqresearch'], $row['lev'] );
		}
		return $requisites;
	}

	/**
	 * Base formula only! Don't use for display build time!
	 * @param int $_nextLevel
	 * @return float
	 */
	public function GetBuildTime($_nextLevel){
		return $this->buildTime + $this->buildTime * $this->timeMultiplier * $_nextLevel;
	}
        
        public function GetPoints($_nextLevel){
            return $this->gainPoints * $_nextLevel * $this->pointLevelMul;
        }
};

class Building {
    public $buildingData, $level, $buildEndTime;

    public function Building( BuildingData $buildingData, $level = 0, $buildEndTime = 0 ){
        $this->buildingData = $buildingData;
        $this->level = $level;
        $this->buildEndTime = $buildEndTime;
    }
};
?>