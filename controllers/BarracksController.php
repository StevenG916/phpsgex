<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\City;
use phpsgex\framework\models\units\UnitData;

class BarracksController extends BaseController {
    public $buildId, $city; /** @var City $city */

    public function __construct()
    {
        $this->city= $this->user->GetCurrentCity();
    }

    public function Train(){
        if( isset($_POST["unitId"]) && !multi_submit() ){
            $unit= UnitData::Instantiate((int)$_POST['unitId']);
            $this->buildId= $unit->build;
            try {
                $this->city->QueueUnits($unit, (int)$_POST['number']);
            } catch(\Exception $ex){
                error_log($ex->getTraceAsString());
                $this->viewData["error"]= $ex->getMessage();
            }
        }

        return $this->Index();
    }

    public function Cancel(){
        if( isset($_GET["queueId"]) ){
            try {
                $this->city->TrainCancel((int)$_GET["queueId"]);
            } catch(\Exception $ex){
                error_log($ex->getTraceAsString());
            }
        }

        $this->Index();
    }

    public function Index(){
        if( isset($_REQUEST["build"]) ) $this->buildId= (int)$_REQUEST["build"];
        parent::Index("barracks");
    }
}
































































































































































