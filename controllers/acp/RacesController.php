<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class RacesController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Index(){
        parent::Index("acp/races");
    }

    public function Delete(){
        if( isset($_GET["id"]) && $_GET["id"] != 1 ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."races
                        where id= ".(int)$_GET["id"]);
        }
        $this->Index();
    }

    public function Create(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            $DB->query("insert into ".TB_PREFIX."races values
                        (null, '{$_POST["name"]}', '".htmlspecialchars($_POST["description"])."',
                        ".sql_fld($_POST["image"]).")");
        }
        $this->Reload();
    }

    public function Edit(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            $DB->query("update ".TB_PREFIX."races
                        set rname= '{$_POST["name"]}', rdesc= '{$_POST["description"]}', img= ".sql_fld($_POST["image"]).
                    " where id= ".(int)$_POST["id"]);
        }
        $this->Reload();
    }
}