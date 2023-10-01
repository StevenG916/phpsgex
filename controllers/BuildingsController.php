<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\buildings\BuildingData;

class BuildingsController extends BaseController{
    public function Build(){
        if( isset($_POST['buildId']) && !multi_submit() ){
            try {
                $build= new BuildingData((int)$_POST['buildId']);
            } catch(\Exception $ex) {
                return $this->Index();
            }

            $this->user->GetCurrentCity()->QueueBuild($build);
            header("Location: ?pg=Buildings");
        }

        return $this->Index();
    }

    public function Cancel(){
        if( !multi_submit("GET") )
            $this->user->GetCurrentCity()->BuildingCancel( (int)$_GET["queueId"] );
        return $this->Index();
    }

    public function Index(){
        parent::Index("buildings");
    }
}