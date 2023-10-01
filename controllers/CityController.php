<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\units\Troop;
use phpsgex\framework\models\units\UnitAction;
use phpsgex\framework\models\units\UnitData;

class CityController extends BaseController{
    /** @var \phpsgex\framework\models\City $city */
    public $city;

    public function Details(){
        if( isset($_GET["id"]) )
            $this->city= \phpsgex\framework\models\City::Instantiate((int)$_GET["id"]);
        else $this->city= $this->user->GetCurrentCity();

        parent::Index("city/details");
    }

    public function Attack(){
        if( empty($_POST) || !isset($_POST["id"]) || multi_submit() )
            return $this->Index();

        $cu= $this->user;
        $atkCityId= (int)$_POST["id"];

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

            $action= ( isset($_POST["support"]) ) ? UnitAction::Support : UnitAction::Attack;
            
            $DB->query("insert into ".TB_PREFIX."units values
                        (null, {$troop->unit->id}, $num, {$cu->id}, {$cu->GetCurrentCity()->id}, $atkCityId, null, ".time().",$arriveTime, $action)");
        }

        header("Location: ?pg=Gathering");
    }

    public function Change(){
        if(isset($_GET["id"])){
            $this->user->SwitchCity((int)$_GET["id"]);
        }

        $this->Index();
    }

    public function Index(){
        parent::Index("city/index");
    }
}