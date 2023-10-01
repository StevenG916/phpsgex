<?php
namespace phpsgex\framework\models;

use phpsgex\framework\battle\BattleReportManager;
use phpsgex\framework\battle\BattleSystem;
use phpsgex\framework\Helper;
use phpsgex\framework\MapCoordinates;
use phpsgex\framework\models\buildings\BuildingData;
use phpsgex\framework\models\buildings\BuildQueue;
use phpsgex\framework\models\research\ResearchData;
use phpsgex\framework\models\units\UnitData;
use phpsgex\framework\models\units\Troop;
use phpsgex\framework\models\units\UnitAction;
use phpsgex\framework\models\units\UnitQueue;
use phpsgex\framework\models\units\UnitType;

if( CITY_SYS == 2 ) require_once("Field.php");

class City {
	public $id, $user, $name, $lastUpdate, $mapPosition, $loyality, $points;
	protected $resources = Array();

    /**
     * @param int $id
     * @param array $_row
     * @param User $_user
     */
	public function __construct( $id = null, Array $_row = null, User $_user = null ){
		if( $_row == null ) {
            if( $id == null ) throw new \Exception("invalid call");

            $this->id= (int)$id;
            global $DB;
            $_row= $DB->query("select * from ".TB_PREFIX."city where id= $id")->fetch_array();
        }

        $this->id = $_row["id"];
        $this->name = $_row["name"];
        $this->lastUpdate = $_row["last_update"];
        $this->loyality = $_row["loyality"];
        $this->points = $_row["points"];

        if( $_user == null && $_row['owner'] != null )
            $this->user= User::Instantiate($_row['owner']);
        else $this->user= $_user;

        switch( MAP_SYS ){
            case 1:
                $this->mapPosition= new MapCoordinates( $_row["x"], $_row["y"], $_row["z"] );
                break;
            case 2:
                $this->mapPosition= new MapCoordinates( $_row["x"], $_row["y"] );
                break;
        }

		$this->DoResourceProduction();
	}

    public static $_instance;

    /**
     * @param null $id
     * @param array|null $_row
     * @param User|null $_user
     * @return City
     */
    public static function Instantiate( $id = null, Array $_row = null, User $_user = null ){
        return call_user_func( self::$_instance, $id, $_row, $_user );
    }

    public function GetResources(){ return $this->resources; }

    public function AddPoints($value){
        $this->points += $value;
        global $DB;
        $DB->query("update ".TB_PREFIX."city 
            set points= {$this->points} 
            where id= ".$this->id);
    }

    /**
     * Only for CITY_SYS 2
     * @return array of buildings foreach field[FieldId] that has MAX_FIELDS elements!
     */
    public function GetFieldsBuildings(){ //TODO edit for listing build in city_build_queue!
        if( CITY_SYS != 2 ) throw new \Exception("this function is only for CITY_SYS 2");
        global $DB;

        $fieldBuild= array();
        for( $i= 0; $i < MAX_FIELDS; $i++ ) $fieldBuild[]= null;

        $qr= $DB->query("select * from ".TB_PREFIX."city_builds where city= ".$this->id);
        while( $row= $qr->fetch_array() ){
            $fieldBuild[ (int)$row['field'] ]= new Field( $row['build'], $row['lev'] );
        }

        return $fieldBuild;
    }

    public static function GetMap2FreeCoords($initPos= NULL){
        global $DB;

        $config= Config::Instance();
        if($initPos == NULL){
            $a= array("nw","ne","sw","se");
            $initPos= $a[mt_rand(0,3)];
        }

        $dev = 0;
        while(true){
            switch($initPos){
                case "ne":
                    $x= mt_rand( $config->Map_max_x /2 +5*$dev, $config->Map_max_x /2 +5*($dev+1) );
                    $y= mt_rand( $config->Map_max_y /2 +5*$dev, $config->Map_max_y /2 +5*($dev+1) );
                    break;
                case "sw":
                    $x= mt_rand( $config->Map_max_x /2 -5*($dev+1), $config->Map_max_x /2 -5*$dev );
                    $y= mt_rand( $config->Map_max_y /2 -5*($dev+1), $config->Map_max_y /2 -5*$dev );
                    break;
                case "se":
                    $x= mt_rand( $config->Map_max_x /2 +5*$dev, $config->Map_max_x /2 +5*($dev+1) );
                    $y= mt_rand( $config->Map_max_y /2 -5*($dev+1), $config->Map_max_y /2 -5*$dev );
                    break;
                default: //nw
                    $x= mt_rand( $config->Map_max_x /2 -5*($dev+1), $config->Map_max_x /2 -5*$dev );
                    $y= mt_rand( $config->Map_max_y /2 +5*$dev, $config->Map_max_y /2 +5*($dev+1) );
                    break;
            }

            $fqr= $DB->query("SELECT count(*) FROM ".TB_PREFIX."city where (x between $x -5 and $x +5) and (y between $y -5 and $y +5)")->fetch_array();

            if( $fqr[0] >= 3.14 * pow($dev, 2) + $dev*2 ){
                $dev++;
                continue;
            }

            $pvc= $DB->query("SELECT * FROM `".TB_PREFIX."city` WHERE `x` = $x AND `y` = $y")->num_rows;
            if($pvc==0) break;
            $dev++;
        }
        return new MapCoordinates($x,$y);
    }

    public static function CreateCity( $_ownerId, MapCoordinates $_coords, $_name ){
        global $DB;

        $owner= $_ownerId != null ? $_ownerId : "null";

        if (MAP_SYS == 1) {
            $x= $_coords->x;
            $y= $_coords->y;
            $z= $_coords->z;
            $newCityQr= $DB->query("INSERT INTO `".TB_PREFIX."city` (`id`, `owner`, `name`, `last_update`, `x`, `y`, `z`) VALUES (null, $owner, '$_name', ".time().", $x, $y, $z);");
        } else {
            $x= $_coords->x;
            $y= $_coords->y;
            $newCityQr= $DB->query("INSERT INTO `".TB_PREFIX."city` (`id`, `owner`, `name`, `last_update`, `x`, `y`) VALUES (null, $owner, '$_name', ".time().", $x, $y);");
        }

        if( !$newCityQr ) throw new \Exception( $DB->error );
        return $DB->insert_id;
    }

    public function GetResourceProductionHour(){
        global $DB;
        $resh= array();
        foreach(ResourceData::GetAll() as $res ){ /** @var ResourceData $res */
            $qrresprodbud= $DB->query("SELECT `id` FROM `".TB_PREFIX."t_builds` WHERE `produceres`= ".$res->id);

            if( $qrresprodbud->num_rows > 0 ) {
                while ($prid = $qrresprodbud->fetch_array()) {
                    $bdlev = $this->GetBuildLevel($prid['id']);
                    if ($bdlev > 0) {
                        $resh[$res->id]= $res->start * $res->productionRate * $bdlev;
                    } else $resh[$res->id]= 0;
                }
            } else $resh[$res->id] = 0;
        }
        return $resh;
    }

    public function DoResourceProduction(){
        global $DB;
        $tmdif= ( time() - $this->lastUpdate ) /3600;

        $procutionhArray= $this->GetResourceProductionHour();
        foreach(ResourceData::GetAll() as $res ){
            $qr= $DB->query("SELECT * FROM `".TB_PREFIX."city_resources` WHERE `city_id`= {$this->id} AND `res_id`= $res->id");
            if( $qr->num_rows ==0 ){
                $DB->query("INSERT INTO `".TB_PREFIX."city_resources` VALUES ({$this->id}, $res->id, {$res->start})");
                $this->resources[$res->id]= $res->start;
            } else {
                $aqr= $qr->fetch_array();
                $this->resources[$res->id]= $aqr['res_quantity'] + $procutionhArray[$res->id] * $tmdif ;
            }
        }

        $this->UpdateResourcesOnDb();
    }

    protected function UpdateLastUpdateTime(){
        global $DB;
        $DB->query("UPDATE ".TB_PREFIX."city SET `last_update`= ".time()." WHERE `id`= {$this->id} LIMIT 1;");
    }

    /**
     * Use only if magazine is activated
     */
    public function GetMaxResourcesCapacity(){
        global $DB;
        $maxcap= Config::Instance()->MG_max_cap;
        if( $maxcap <= 0 ) throw new \Exception("Magazine is not activated! MG_max_cap must be > 0!");

        if($this->user== null) return $maxcap; //TODO abbandoned village magazine?

        $qtmg= $DB->query("SELECT id FROM ".TB_PREFIX."t_builds WHERE func= 'mag_e' and (arac= {$this->user->raceId} or arac is null)");
        if( $qtmg->num_rows >0 ) {
            $tmg= $qtmg->fetch_array();
            $maglev= $this->GetBuildLevel($tmg['id']); //magazine level

            $maxres= $maxcap * ($maglev +1) * Config::Instance()->MG_mul;
        } else {
            error_log("WARNING: Magazine is activated but there is no building with magazine function!");
            $maxres= $maxcap;
        }
        return $maxres;
    }

	public function UpdateResourcesOnDb(){
        global $DB;

        if( Config::Instance()->MG_max_cap >0 ){
            $maxres= $this->GetMaxResourcesCapacity();
        }

        for( $i=1; $i <= count( $this->resources ); $i++ ){
            if( isset($maxres) ){ //magazine engine
                if( $this->resources[$i] > $maxres ) $this->resources[$i]= $maxres;
            }

            $DB->query("UPDATE `".TB_PREFIX."city_resources` SET `res_quantity`= {$this->resources[$i]} WHERE `city_id`= {$this->id} AND `res_id`= $i;");
        }

        $this->UpdateLastUpdateTime();
	}

    /**
     * get building level, 0 if not built
     * @param int $_buildId
     * @param bool $_queue if true, will be added 1 to the level foreach building in the queue
     * @return int build level
     */
	public function GetBuildLevel( $_buildId, $_queue = false, $_field= -1 ){
		global $DB;

        if( CITY_SYS == 1 )
		    $qrbuildedinfo= $DB->query("SELECT lev FROM ".TB_PREFIX."city_builds WHERE build= $_buildId AND city= {$this->id} LIMIT 1;");
		else {
            if( $_field < 0 )
                $qrbuildedinfo= $DB->query("SELECT lev FROM ".TB_PREFIX."city_builds WHERE build= $_buildId AND city= {$this->id} order by lev desc LIMIT 1;");
            else
                $qrbuildedinfo= $DB->query("SELECT lev FROM ".TB_PREFIX."city_builds WHERE build= $_buildId AND city= {$this->id} and field= $_field LIMIT 1;");
            //echo "<h1>SELECT lev FROM ".TB_PREFIX."city_builds WHERE build= $_buildId AND city= {$this->id} and field= $_field LIMIT 1</h1>";
        }

        if( $qrbuildedinfo->num_rows >0 ) {
			$abli= $qrbuildedinfo->fetch_array();
			$lev= $abli['lev'];
		} else $lev= 0;
		
		if( !$_queue ) return $lev;
		else {
			$qr= $DB->query("SELECT id FROM `".TB_PREFIX."city_build_que` WHERE `city`= {$this->id} AND `build` = ".$_buildId);
			return $lev + $qr->num_rows;
		}
	}

    public function GetBuildQueue(){
        global $DB;
        $ret= Array();
        $qr= $DB->query("select * from ".TB_PREFIX."city_build_que where city= {$this->id} and `end` > ".time()." order by `end` asc");
        while($row= $qr->fetch_array()){
            $ret[]= new BuildQueue($row);
        }
        return $ret;
    }

    public function GetBuildQueueTime( $buildId= null ){
        global $DB;
        $qr= $DB->query("select max(`end`) from ".TB_PREFIX."city_build_que where city= {$this->id} "
            .($buildId== null ? "" : "and build= $buildId") );

        $t= $qr->fetch_array()[0];
        return $t ==0 ? $t : $t -time();
    }

    //you can build if count($missings) is == 0
	public function CanBuild_BuildRequirements( BuildingData $_build ){
        global $DB;
        $requirements = $_build->GetBuildingsRequirements();
        $missings = Array();

        foreach ($requirements as $req) {
            $query= $DB->query( "select id from ".TB_PREFIX."city_builds where city= {$this->id} and build= {$req->buildId} and lev >= ".$req->level );
            if( $query->num_rows == 0 ) $missings[] = $req;
        }

        return $missings;
	}

    public function CanBuild_ResearchesRequirements( BuildingData $_build ){
        global $DB;
        $requirements = $_build->GetResearchRequirements();
        $missings = Array();

        foreach($requirements as $req){
            $query = new $DB->query( "select id from ".TB_PREFIX."user_research where id_res= ".$req->researchId." and usr= {$this->user->id} and lev >= ".$req->level );
            if( $query->num_rows == 0 ) $missings[] = $req;
        }

        return $missings;
    }

    public function CanBuild_ResourcesCheck( BuildingData $_build, $_nextLevel ){
        $requestedResources = $_build->GetBuildCosts( $_nextLevel );

        for($i=1; $i <= count( $this->resources ); $i++){
            if( $this->resources[$i] < $requestedResources[$i] ) return false;
        }

        return true;
    }

    /**
     * get building time of a build
     * @param BuildingData $_build
     * @param int $_nextLevel level to be built
     * @return int building time in seconds
     * @throws \Exception
     */
    public function GetBuildTime( BuildingData $_build, $_nextLevel ){
        if( $_nextLevel <=0 ) throw new \Exception( "nextLevel <= 0 !" );

        global $DB;
        $buildFasterBuildQr= $DB->query("select lev from ".TB_PREFIX."t_builds join ".TB_PREFIX."city_builds on (".TB_PREFIX."t_builds.id=".TB_PREFIX."city_builds.build) where func= 'buildfaster' limit 1");
        if( $buildFasterBuildQr->num_rows == 0 ) $buildFasterLev= 0;
        else {
			$a= $buildFasterBuildQr->fetch_array();
			$buildFasterLev= $a['lev'];
		}

        $buildTime= $_build->GetBuildTime( $_nextLevel );

        if( $buildFasterLev != 0 )
            $buildTime /= ($buildFasterLev * Config::Instance()->buildfast_molt);

        return (int)$buildTime;
    }

    /**
     * add a building to Queue subtracting resources, it will be built next level
     * @param BuildingData $_build
     * @param int $_field
     * @throws MissingRequirementsException
     * @throws \Exception
     */
	public function QueueBuild( BuildingData $_build, $_field= 0 ){
        $buildQueue= $this->GetBuildQueue();
        if( Helper::IsBuildQueueFull($buildQueue) )
            throw new \Exception("Building queue is full");

        if( CITY_SYS == 1 )
		    $nextQueueLevel= $this->GetBuildLevel( $_build->id, true ) +1;
        else
            $nextQueueLevel= $this->GetBuildLevel( $_build->id, true, $_field ) +1;

        if( $_build->maxLevel != 0 && $nextQueueLevel > $_build->maxLevel )
            throw new \Exception("Max level is reached");

        if( !$this->CanBuild_ResourcesCheck( $_build, $nextQueueLevel ) ) throw new MissingRequirementsException();
        if( count( $this->CanBuild_BuildRequirements( $_build ) ) != 0 ) throw new MissingRequirementsException();
        if( count( $this->CanBuild_ResearchesRequirements( $_build ) ) != 0 ) throw new MissingRequirementsException();

        global $DB;
        $costs= $_build->GetBuildCosts( $nextQueueLevel );
        foreach( $costs as $i => $c ) $this->resources[$i] -= $c;
        $this->UpdateResourcesOnDb();
        //true if building can run in parallel
        $parallel= Config::Instance()->build_que_parallel == 0 || Config::Instance()->build_que_parallel < count($buildQueue);
        $queueTime= $this->GetBuildQueueTime( $parallel ? $_build->id : null );
        $buildEndTime= $this->GetBuildTime($_build, $nextQueueLevel) +$queueTime +time();
        if( CITY_SYS == 1 )
            $DB->query("insert into ".TB_PREFIX."city_build_que values (null, {$this->id}, {$_build->id}, $nextQueueLevel, $buildEndTime)");
        else {
            $qr= $DB->query("select id from ".TB_PREFIX."city_build_que where city= {$this->id} and `field`= $_field and build != ".$_build->id);
            if($qr->num_rows > 0) throw new \Exception("field is occupied!");

            $qr= $DB->query("select id from ".TB_PREFIX."city_builds where city= {$this->id} and `field`= $_field and build != ".$_build->id);
            if($qr->num_rows > 0) throw new \Exception("field is occupied!");

            $DB->query("insert into ".TB_PREFIX."city_build_que values (null, {$this->id}, {$_build->id}, $buildEndTime, $nextQueueLevel, $_field)");
        }
	}

    public function SetBuildLevel($buildId, $lev, $field= 0){
        if($lev<=0) throw new \Exception("Level is <= 0!");
        global $DB;

        if($lev==1)
            $DB->query("INSERT INTO `".TB_PREFIX."city_builds`
                        VALUES ( NULL, {$this->id}, $buildId, 1".( CITY_SYS == 2 ? ", $field" : "" )." );");
        else
            $DB->query("UPDATE `".TB_PREFIX."city_builds` SET `lev`= $lev
                        WHERE `build`= $buildId AND city= {$this->id} ".( CITY_SYS == 2 ? "and field= $field" : "" )." LIMIT 1");
    }

    protected function FetchBuildQueue(){
        global $DB; $config= Config::Instance();
        //search if there is a building that is completed in the que and process it
        $bqs= $DB->query("SELECT * FROM ".TB_PREFIX."city_build_que 
            WHERE `city`= {$this->id} and `end` <= ".time());
        while( $queuedBuild= $bqs->fetch_array() ) {
            $DB->query("DELETE FROM `".TB_PREFIX."city_build_que` 
                WHERE `id`= ".$queuedBuild['id']);
            //build level control!
            $nextLev= $this->GetBuildLevel( $queuedBuild['build'] ) +1;

            if(CITY_SYS == 1)
                $this->SetBuildLevel($queuedBuild['build'], $nextLev);
            else
                $this->SetBuildLevel($queuedBuild['build'], $nextLev, $queuedBuild['filed']);

            //pop increment engine
            //$opobd= $DB->query("SELECT `id` FROM `".TB_PREFIX."t_builds` WHERE `func` = 'pop_e' LIMIT 1;")->fetch_array();
            //if( $opobd['id'] == $rab['build'] ) $this->AddPopulation($value);

            //add points
            $build= new BuildingData( $queuedBuild['build'] );
            $this->AddPoints( $build->GetPoints($nextLev) );
            
            if( $build->function == "pop_e" && $config->popres != null ){ //add population
                $popRes= new ResourceData($config->popres);
                $this->resources[$config->popres] += $popRes->start * $nextLev * $popRes->productionRate;
            }
        }
    }

    /**
     * check research buildings requirements
     * @param ResearchData $_research
     * @return array empty if the research matches the requirements, otherwise it contains the missing requirements
     */
	public function CanResearch_BuildRequirements( ResearchData $_research ){
        global $DB;
        $requirements= $_research->GetBuildingsRequirements();
        $missings= array();

        foreach ($requirements as $req) {
            $query= $DB->query( "select id from ".TB_PREFIX."city_builds where city = ".$this->id." and build = ".$req->buildId." and lev >= ".$req->level );
            if( $query->num_rows == 0 ) $missings[]= $req;
        }

        return $missings;
	}

    public function CanResearch_ResearchesRequirements( ResearchData $_research ){
        global $DB;
        $requirements= $_research->GetResearchRequirements();
        $missings= Array();

        foreach( $requirements as $req ){
            $query = new $DB->query( "select id from ".TB_PREFIX."user_research where id_res = ".$req->researchId." and usr = ".$this->user->id." and lev >= ".$req->level );
            if( $query->num_rows == 0 ) $missings[] = $req;
        }

        return $missings;
    }

    public function CanResearch_ResourcesCheck( ResearchData $_research, $_nextLevel ){
        $requestedResources= $_research->GetResearchCosts( $_nextLevel );

        for($i=1; $i <= count( $this->resources ); $i++){
            if( $this->resources[$i] < $requestedResources[$i] ) return false;
        }

        return true;
    }

    /**
     * add a research to Queue subtracting resources, it will be built next level
	 * @throws MissingRequirementsException if you can't build
     */
	public function QueueResearch( ResearchData $_research ){
        $nextQueueLevel= $this->user->GetResearchLevel( $_research->id, true ) +1;
        if( !$this->CanResearch_ResourcesCheck( $_research, $nextQueueLevel ) ) throw new MissingRequirementsException();
        if( count( $this->CanResearch_BuildRequirements( $_research ) ) != 0 ) throw new MissingRequirementsException();
        if( count( $this->CanResearch_ResearchesRequirements( $_research ) ) != 0 ) throw new MissingRequirementsException();

        global $DB;
        foreach( $_research->GetResearchCosts( $nextQueueLevel ) as $resId => $c)
            $this->resources[$resId] -= $c;
        $this->UpdateResourcesOnDb();

        $resEndTime= $_research->GetResearchTime( $nextQueueLevel ) +$this->user->GetResearchQueueTime( $_research->id ) +time();
        $DB->query("insert into ".TB_PREFIX."city_research_que values (null, {$this->id}, {$_research->id}, $nextQueueLevel, $resEndTime)");
	}

    public function CanTrain_BuildRequirements( UnitData $_unit ){
        global $DB;
        $requirements= $_unit->GetBuildingsRequirements();
        $missings= Array();

        foreach($requirements as $req) {
            $query= $DB->query("select id from ".TB_PREFIX."city_builds
                                where city= {$this->id} and build= {$req->buildId} and lev >= ".$req->level);
            if( $query->num_rows == 0 ) $missings[]= $req;
        }

        return $missings;
    }

    public function CanTrain_ResearchRequirements( UnitData $_unit ){
        global $DB;
        $requirements=  $_unit->GetResearchRequirements();
        $missings= Array();

        foreach($requirements as $req){
            $query= $DB->query( "select * from ".TB_PREFIX."user_research where id_res = ".$req->researchId." and usr = ".$this->user->id." and lev >= ".$req->level );
            if( $query->num_rows == 0 ) $missings[] = $req;
        }

        return $missings;
    }

    public function GetMaxTrainableUnits( UnitData $_unit ){
        $maxunt=0; $f=1;

        $costs= $_unit->GetTrainCosts();
        for( $i= 1; $i<= count($this->resources); $i++ ){
            if( $costs[$i] ==0 ) continue;
            $mtv= (int)($this->resources[$i] / $costs[$i]);
            if( $f == 1 ){ $maxunt= $mtv; $f++; }
            else if( $mtv < $maxunt ) $maxunt= $mtv;
        }

        $popres= Config::Instance()->popres;
        if( $popres != null && $maxunt > $this->resources[$popres] ) {
            if( $_unit->GetTrainCosts()[$popres] == 0 ){
                error_log("WARNING: population for {$_unit->name} is 0!");
                return INF;
            } else $maxunt= $this->resources[$popres] / $_unit->GetTrainCosts()[$popres];
        }

        if( $_unit->type == UnitType::Hero )
            return min(1, (int)$maxunt);

        return (int) $maxunt;
    }

    /**
     * @param UnitData $_unit
     * @param int $_quantity
     * @return int
     */
    public function GetUnitTrainTime( UnitData $_unit, $_quantity= 1 ){ //TODO improve consider barracks level reduction
        return $_unit->trainTime * $_quantity;
    }

    public function QueueUnits( UnitData $_unit, $_quantity= 1 ){
        if( $_quantity < 1 ) throw new \Exception("quantity < 1! given quantity= $_quantity");
        global $DB;

        $mxunt= $this->GetMaxTrainableUnits( $_unit );

        if( $_unit->type == UnitType::Hero ){
            $qr= $DB->query("select sum(uqnt) from ".TB_PREFIX."units 
                                    where id_unt= {$_unit->id} and owner_id= ".$this->user->id);
            return $qr->fetch_array()[0] > 0 ? 0 : 1;
        }

        if( $mxunt ==0 ) return;
        $_quantity= min( $_quantity, $mxunt );

        //queue time
        $queue= $this->GetTrainUnitsQueue($_unit->build);

        if( Config::Instance()->unit_que_max != 0 && count($queue) >= Config::Instance()->unit_que_max )
            throw new \Exception("Queue is full");

        $queueTime= 0;
        if( Config::Instance()->unit_que_parallel >0 ) {
            $i= 1;
            foreach( $queue as $uq ) {
                $diff= $uq->end -time();
                if( $diff >0 ){
                    if( $i++ <= Config::Instance()->unit_que_parallel )
                        continue;

                    $queueTime += $diff;
                }
            }
        }

        $timeend= time() +$queueTime +$this->GetUnitTrainTime( $_unit, $_quantity );

        foreach( $_unit->GetTrainCosts( $_quantity ) as $resId => $cost )
            $this->resources[$resId] -= $cost;
        $this->UpdateResourcesOnDb();

        $DB->query("INSERT INTO `".TB_PREFIX."unit_que` (`id` ,`id_unt` ,`uqnt` ,`city` ,`end`)
                    VALUES (NULL, {$_unit->id}, $_quantity, {$this->id}, $timeend);");
    }

    public function GetTrainUnitsQueue( $buildId= null ){
        global $DB;
        $ret= Array();

        $qrStr= "select q.*, u.build
                  from ".TB_PREFIX."unit_que as q join ".TB_PREFIX."t_unt as u on (u.id=q.id_unt)
                  where city= {$this->id}";

        if($buildId != null)
            $qrStr.= " and build= $buildId";

        $qr= $DB->query($qrStr." order by `end` asc");
        while( $row= $qr->fetch_array() ){
            $unitData= UnitData::Instantiate( $row["id_unt"] );
            //$ret[]= new UnitQueue($unitData, $row["uqnt"], $row['end']);
            $ret[]= UnitQueue::Instantiate($row["id"], $row, $unitData);
        }

        return $ret;
    }

    public function FetchTrainUnitsQueue(){ //TODO replace with GetTrainUnitsQueue
        global $DB;

        $qr= $DB->query("select q.*, u.build
                          from ".TB_PREFIX."unit_que as q join ".TB_PREFIX."t_unt as u on (u.id=q.id_unt)
                            where city= ".$this->id);
        while( $row= $qr->fetch_array() ){
            $unitData= UnitData::Instantiate( $row["id_unt"] );
            $originalTrainTime= $this->GetUnitTrainTime( $unitData );
            $timeDiff= $row['end'] - time();

            if( $timeDiff <= 0 ) $unitTrained= $row["uqnt"];
            else {
                $unitTrained= ($originalTrainTime * $row["uqnt"] - $timeDiff);
                if( $unitTrained <= 0 ) continue; //shouldn't happen!
                $unitTrained /= $originalTrainTime;
                $unitTrained= (int)$unitTrained;
            }

            if( $unitTrained <= 0 ) continue;

            if( $row["uqnt"] == $unitTrained || $timeDiff <= 0 )
                $DB->query("delete from ".TB_PREFIX."unit_que where id= ".$row['id']);
            else
                $DB->query("update ".TB_PREFIX."unit_que set uqnt= (uqnt - $unitTrained) where id= ".$row["id"]);

            $CityUnitQr= $DB->query("select * from ".TB_PREFIX."units 
                where owner_id= {$this->user->id} and id_unt= {$row['id_unt']} and `where`= ".$this->id);
            if( $CityUnitQr->num_rows ==0 ) {
                $DB->query("insert into ".TB_PREFIX."units values
                    ( null, {$row['id_unt']}, {$row['uqnt']}, {$this->user->id}, null, null, {$this->id}, null, null, ".UnitAction::Re_entry." )");
            } else {
                $ainf= $CityUnitQr->fetch_array();
                $DB->query("update ".TB_PREFIX."units set uqnt= (uqnt + $unitTrained) where id= ".$ainf['id']);
            }
        }
    }

    public function FetchAttacksToSelf(){
        global $DB;

        $qr= $DB->query("select `time`, owner_id
                         from ".TB_PREFIX."units
                         where `to`= {$this->id} and `time` <= ".time()." and `action`= ".UnitAction::Attack."
                         group by `time`, owner_id
                         order by `time` asc");

		 $cityUnits= $this->GetTroops();

        while( $row= $qr->fetch_array() ) {
            $atkQr= $DB->query("
              select * from ".TB_PREFIX."units
              where `to`= {$this->id} and `action`= ".UnitAction::Attack." and `time`= {$row["time"]} and owner_id= {$row["owner_id"]}");
            $atackingUnits= array();
            while( $atkRow= $atkQr->fetch_array() ){
                $atackingUnits[]= Troop::Instantiate(0, $atkRow);
            }

            $result= BattleSystem::Instance()->Battle($atackingUnits, $cityUnits, $this);
            BattleReportManager::Instance()->GenerateReport($result);

            foreach( $result->defUnitsLost as $i => $qnt) /** @var Troop $unit */
                $cityUnits[$i]->quantity -= $qnt;

            $atackerCity= City::Instantiate($atackingUnits[0]->from);

            $minSpeed= 99;
            foreach( $result->atkUnits as $i => $unit ){
                if( !array_key_exists($i, $result->atkUnitsLost) || $result->atkUnitsLost[$i] < $unit->quantity )
                    $minSpeed= min($minSpeed, $unit->unit->speed);
            }
            $arriveTime= time() + $this->mapPosition->GetDistance($atackerCity->mapPosition) * 99/$minSpeed;

            foreach( $result->atkUnits as $i => $unit ){
                $quantity= $unit->quantity;
                if( array_key_exists($i, $result->atkUnitsLost) && $result->atkUnitsLost[$i] >0 ) {
                    $quantity -= $result->atkUnitsLost[$i];

                    if( Config::Instance()->popres != null ){ //population
                        $trainCosts= $unit->unit->GetTrainCosts($result->atkUnitsLost[$i]);

                        if( $this->user != null )
                            $this->user->AddAtkPoints( $trainCosts[Config::Instance()->popres] );

                        $atackerCity->resources[Config::Instance()->popres] +=
                            $trainCosts[Config::Instance()->popres];
                    }
                }

                $quantity= max($quantity, 0);

                if($quantity >0)
                    $DB->query("update ".TB_PREFIX."units 
                        set `from`= {$unit->to}, `to`= {$unit->from}, 
                        uqnt= $quantity, action= ".UnitAction::Re_entry.", `time`= $arriveTime 
                        where id= ".$unit->id);
                else
                    $DB->query("delete from ".TB_PREFIX."units 
                                    where id= ".$unit->id);

                $atackerCity->UpdateResourcesOnDb();
            }

            foreach( $result->defUnits as $i => $unit ){
                if( !array_key_exists($i, $result->defUnitsLost) && $result->defUnitsLost[$i] >0 )
                    continue;

                $quantity= $unit->quantity - $result->defUnitsLost[$i];
                $quantity= max($quantity, 0);

                if( $quantity >0 )
                    $DB->query("update " . TB_PREFIX . "units 
                    set uqnt= {$unit->quantity} 
                    where id= " . $unit->id);
                else
                    $DB->query("delete from ".TB_PREFIX."units 
                                    where id= ".$unit->id);

                if( Config::Instance()->popres != null ){ //population
                    $trainCosts= $unit->unit->GetTrainCosts($result->defUnitsLost[$i]);

                    if( $unit->from == null ) //unit owned by current town
                        $this->resources[Config::Instance()->popres] +=
                            $trainCosts[Config::Instance()->popres];
                    else { //support from other town
                        $city= City::Instantiate($unit->from);
                        $city->resources[Config::Instance()->popres] +=
                            $trainCosts[Config::Instance()->popres];
                        $city->UpdateResourcesOnDb();
                    }

                    $atackerCity->user->AddAtkPoints( $trainCosts[Config::Instance()->popres] );
                }
            }

            $this->UpdateResourcesOnDb();
        }
    }

    /**
     * attack to others
     */
    public function FetchUnitMovements(){
        if( $this->user == null ) return;
        global $DB;
        //return to attack
        $qr= $DB->query("SELECT * FROM `".TB_PREFIX."units`
                         WHERE `owner_id`= {$this->user->id} AND `action`= ".UnitAction::Attack." AND `time` <= ".time());
        while( $row= $qr->fetch_array() ){
            City::Instantiate( $row['to'] )->FetchAllQueue();
        } 
        //return units
        $qr= $DB->query("SELECT * FROM `".TB_PREFIX."units`
                         WHERE `to`= {$this->id} AND `time` <= ".time()." AND `where` is null ");
        while( $row= $qr->fetch_array() ){
            if( $row["action"] == UnitAction::Re_entry ){ //your units
                $qrcuruntct= $DB->query("SELECT * FROM `".TB_PREFIX."units`
                                         WHERE `owner_id`= {$this->user->id} AND `where`= {$this->id} AND `id_unt`= ".$row['id_unt']);
                
                if( $qrcuruntct->num_rows ==0 )
                    $DB->query("INSERT INTO `".TB_PREFIX."units` (`id`, `id_unt`, `uqnt`, `owner_id`, `from`, `to`, `where`, `startTime`, `time`, `action`) VALUES
                                (NULL, {$row['id_unt']}, {$row['uqnt']}, {$this->user->id}, NULL, NULL, {$this->id}, null, null, null, ".UnitAction::Re_entry.");");
                else {
                    $ayu= $qrcuruntct->fetch_array();
                    $totunt= $ayu['uqnt'] +$row['uqnt'];
                    $DB->query("UPDATE `".TB_PREFIX."units` SET `uqnt`= $totunt WHERE `id`= ".$ayu['id']);
                }
                
                $DB->query("DELETE FROM `".TB_PREFIX."units`
                            WHERE `id`= ".$row['id']);
            } else { //support
                $DB->query("update ".TB_PREFIX."units "
                        . "set `where`= {$this->id}, time= null"
                        . "where id= ".$row["id"]);
            }

            if($DB->error) error_log($DB->error);
        }

        $DB->query("delete from ".TB_PREFIX."units where uqnt <= 0");
		
    }


    /**
     * get units in city
     * @return Troop array
     */
    public function GetTroops(){
        global $DB;
        $ret= array();

        $quyrunt= $DB->query("SELECT ".TB_PREFIX."units.*, ".TB_PREFIX."t_unt.vel
                              FROM ".TB_PREFIX."units JOIN ".TB_PREFIX."t_unt ON (".TB_PREFIX."units.id_unt = ".TB_PREFIX."t_unt.id)
                              WHERE `where`= {$this->id}
                              ORDER BY `".TB_PREFIX."t_unt`.`vel` DESC");
        if( $quyrunt->num_rows > 0 ) {
            while ($row= $quyrunt->fetch_array()) {
                $ret[(int)$row['id']]= Troop::Instantiate( 0, $row );
            }
        }

        return $ret;
    }

    public function GetUnitQuantityByTUnitId($id_unt){
        global $DB;
        $qr= $DB->query("select sum(uqnt) 
                    from ".TB_PREFIX."units 
                    where id_unt= $id_unt and `where`= {$this->id} and `action`= ".UnitAction::Re_entry);

        return (int) $qr->fetch_array()[0];
    }

    public function FetchAllQueue(){
        $this->FetchBuildQueue();
        $this->FetchTrainUnitsQueue();
        $this->FetchAttacksToSelf();
        $this->FetchUnitMovements();
    }

    /**
     * @deprecated
     * @param $offerId
     */
    public function Market_MakeOffer( $requestedResId, $requestedResAmount, $offeredResId, $offeredResAmount ){
        $resoffqnt= (int)min( $offeredResAmount, $this->resources[$offeredResId] );
        if( $resoffqnt > 0 ) {
            global $DB;
            $this->resources[$offeredResId] -= $resoffqnt;
            $DB->query("INSERT INTO `".TB_PREFIX."market` (`id`, `city`, `resoff`, `resoqnt`, `resreq`, `resrqnt`)
                        values (NULL, ".$this->id.", $offeredResId, $resoffqnt, $requestedResId, $requestedResAmount);");
            $this->UpdateResourcesOnDb();
        }
    }

    /**
     * @deprecated
     * @param $offerId
     */
    public function Market_AcceptOffer( $offerId ){
        global $DB;
        $offinf= $DB->query("SELECT * FROM ".TB_PREFIX."market WHERE id=".$offerId)->fetch_array();
        if( $this->resources[ $offinf['resreq'] ] >= $offinf['resrqnt'] ){ //you must have the requided resoure!
            $DB->query("DELETE FROM ".TB_PREFIX."market WHERE id=".$offerId);
            if( $DB->affected_rows == 0 ) return; //offer already accepted!

            $this->resources[ $offinf['resreq'] ] -= $offinf['resrqnt'];
            $this->resources[ $offinf['resoff'] ] += $offinf['resoqnt'];
            $this->UpdateResourcesOnDb();

            $op= City::Instantiate( $offinf['city'] ); //update the offer owner resource!
            $op->resources[ $offinf['resreq'] ] += $offinf['resrqnt'];
            $op->UpdateResourcesOnDb();
        }
    }

    /**
     * @deprecated
     * @param $offerId
     */
    public function Market_DeleteOffer( $offerId ){
        global $DB;
        $offdata= $DB->query("SELECT * FROM ".TB_PREFIX."market
                                WHERE id= ".$offerId)->fetch_array();
        $DB->query("DELETE FROM ".TB_PREFIX."market
                    WHERE id= ".$offerId);
        if( $DB->affected_rows == 0 ) return; //offer already accepted!

        $this->resources[ $offdata['resoff'] ] += $offdata['resoqnt'];
        $this->UpdateResourcesOnDb();
    }

    public function BuildingCancel( $queue_id ){
        global $DB;
        //$DB->query("delete from ".TB_PREFIX."city_build_que where city= {$this->id} and id= $queue_id");
        $qr= $DB->query("select b.* from ".TB_PREFIX."city_build_que as b, (select build, `level` from ".TB_PREFIX."city_build_que
                                                                      where id= $queue_id) as q
                        where city= {$this->id} and b.build = q.build and b.level >= q.level");
        $build= null;
        while($row= $qr->fetch_array()){
            if( $build == null ) $build= new BuildingData($row["build"]);

            foreach( $build->GetBuildCosts($row["level"]) as $resId => $cost )
                $this->resources[$resId] += $cost;

            $DB->query("delete from ".TB_PREFIX."city_build_que where id= ".$row["id"]);
        }

        $this->UpdateResourcesOnDb();
    }

    public function TrainCancel( $queueId ){
        global $DB;

        $queue= UnitQueue::Instantiate( $queueId );
        $untCosts= $queue->unit->GetTrainCosts($queue->number);

        $DB->query("delete from ".TB_PREFIX."unit_que where id= $queueId");
        //get back costs
        foreach( ResourceData::GetAll() as $res ){
            $this->resources[$res->id] += $untCosts[$res->id];
        }
        $this->UpdateResourcesOnDb();
    }
};

City::$_instance= function($id = null, Array $row = null, User $usr = null){ return new City($id, $row, $usr); };