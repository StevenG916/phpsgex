<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class ResourcesController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Index(){
        parent::Index("acp/resources");
    }

    public function Create(){
        if( count($_POST) >0 ){
            global $DB;
            $DB->query("insert into ".TB_PREFIX."resdata
            values(null, '{$_POST["name"]}', {$_POST["production"]}, {$_POST["start"]}, ".sql_fld($_POST["icon"]).")");
        }

        $this->Reload();
    }

    public function Edit(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            foreach( $_POST["name"] as $id => $name ){
                $DB->query("update ".TB_PREFIX."resdata
                set name= '$name',
                    start= {$_POST["start"][$id]},
                    prod_rate= {$_POST["production"][$id]},
                    ico= ".( array_key_exists($_POST["icon"], $id) ? "'{$_POST["icon"][$id]}'" : "null" )."
                where id= $id");
            }
        }

        $this->Index();
    }

    public function Delete(){
        if( isset($_GET["id"]) && $_GET["id"] >1 ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."resdata where id= ".(int)$_GET["id"]);
        }
        $this->Index();
    }
}