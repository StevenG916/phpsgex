<?php

namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class ConfigsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Edit(){
        global $DB;

        $DB->query("update ".TB_PREFIX."conf 
            set popres= {$_POST["popres"]}, serverEnd= STR_TO_DATE({$_POST["serverEnd"]}, '%d/%m/%Y') 
            limit 1");

        $this->Index();
    }

    public function Index(){
        parent::Index("acp/configs");
    }
}