<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\research\ResearchData;

class ResearchController extends BaseController{
    public function Research(){
        if( isset($_POST['researchId']) && !multi_submit() ){
            try {
                $research= new ResearchData((int)$_POST['researchId']);
            } catch(\Exception $ex) {
                return $this->Index();
            }

            $this->user->GetCurrentCity()->QueueResearch($research);
            header("Location: ?pg=Research");
        }

        return $this->Index();
    }

    public function Index(){
        parent::Index("research");
    }
}