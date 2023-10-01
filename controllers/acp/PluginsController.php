<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class PluginsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Enable(){
        if(isset($_GET["p"])){
            global $DB;
            $DB->query("update ".TB_PREFIX."plugins
                        set active= true
                        where name= '{$_GET["p"]}'");
        }

        $this->Index();
    }

    public function Disable(){
        if(isset($_GET["p"])){
            global $DB;
            $DB->query("update ".TB_PREFIX."plugins
                        set active= false
                        where name= '{$_GET["p"]}'");
        }

        $this->Index();
    }

    public function Index(){
        parent::Index("acp/plugins");
    }
}