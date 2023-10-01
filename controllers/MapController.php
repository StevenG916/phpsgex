<?php
namespace phpsgex\controllers;
use \phpsgex\framework\Auth;
use \phpsgex\framework\models\Config;
use \phpsgex\framework\models\MapCityImage;
use \phpsgex\framework\models\City;

class MapCell {
	public $x, $y, $type, $villageId, $villageName, $playerId, $playerName, 
                $allyId, $allyName, $relation, $css;

	public function __construct($x, $y, City $city= null){
		global $DB;
		$this->x= $x;
		$this->y= $y;

		if($city==null) return;
		$this->type= "city";
		$this->villageId= $city->id;
		$this->villageName= $city->name;
        $this->relation= "none";
                
        if( $city->user == null ){
            $this->css= MapCityImage::GetImage($city->points, $city->IsBonus(), true);
            return;
        }

		$this->playerId= $city->user->id;
		$this->playerName= $city->user->name;
		$this->allyId= $city->user->allyId;

        if( $this->allyId != null ){
            $qra= $DB->query("select name from ".TB_PREFIX."ally "
                            ."where id= ".$this->allyId)->fetch_array();
            $this->allyName= $qra["name"];
        }
                
		if( $this->playerId == $this->user->id )
			$this->relation= "own";
		else if($this->allyId != null && $this->allyId == $this->user->allyId) //TODO check ally pacts
			$this->relation= "ally";

        $this->css= MapCityImage::GetImage($city->points, $city->IsBonus());
	}
}

class MapController extends BaseController{
	public $x, $y, $mapSizeX= 14, $mapSizeY= 10;

	public function Get(){
		if( isset($_GET["x"]) ){
			$x= (int)$_GET["x"];
			$y= (int)$_GET["y"];
		} else {
			$city= $this->user->GetCurrentCity();
			$x= $city->mapPosition->x;
			$y= $city->mapPosition->y;
		}

		if( isset($_GET["size"]) ){
			$this->mapSizeX= (int)$_GET["size"];
			$this->mapSizeY= (int)$_GET["size"];
		}

		$grid= Array();
		for($r= 0; $r< $this->mapSizeY; $r++) $grid[]= Array();
		global $DB;

		for($r= 0; $r< $this->mapSizeY; $r++){
			for($c= 0; $c< $this->mapSizeX; $c++){
				$qr= $DB->query("select * from ".TB_PREFIX."city
								where x= ".($x+$c)." and y= ".($y+$r));
				if( $qr->num_rows ==0 ){
					$grid[$r][$c]= new MapCell($x+$c, $y+$r);
				} else {
					$aqr= $qr->fetch_array();
					$grid[$r][$c]= new MapCell($x+$c, $y+$r, City::Instantiate(null, $aqr));
				}
			}
		}

		echo json_encode($grid);
	}

	public function GetRandomElements(){
		$ret= array();
		$elements= ["e1", "e2", "e3"]; $e= 0;

		srand(22);
		for($i= 0; $i< 300; $i++) {
			$x= rand(0, Config::Instance()->Map_max_x);
			if( !array_key_exists($x, $ret) )
				$ret[$x] = array();

			for($j=0; $j < 300; $j++)
				$ret[$x][ rand(0, Config::Instance()->Map_max_y) ]= $elements[ $e++ % count($elements) ];
		}

		echo json_encode($ret);
	}

    public function Index(){
		if( isset($_GET["x"]) ){
			$this->x= (int)$_GET["x"];
			$this->y= (int)$_GET["y"];
		} else {
			$city= $this->user->GetCurrentCity();
			$this->x= $city->mapPosition->x;
			$this->y= $city->mapPosition->y;
		}
		
        parent::Index("map");
    }
}