<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;
use phpsgex\framework\models\MapCityImage;

class MapCityImageController extends BaseController{
    public $mapCityImages= [];

    public function __construct()
    {
        parent::__construct();
        $this->requiredRank= 2;
    }

    public function Create(){
        if(isset($_POST["points"]) && !multi_submit()){
            global $DB;

            $abbandoned= isset($_POST["abbandoned"]) ? "true" : "false";
            $bonus= isset($_POST["bonus"]) ? "true" : "false";

            $DB->query("insert into ".TB_PREFIX."mapcityimage values 
                (null, {$_POST["points"]}, {$_POST["image"]}, $abbandoned, $bonus)");
        }

        $this->Index();
    }

    public function Delete(){
        if( isset($_GET["id"]) ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."mapcityimage where id= ".(int)$_GET["id"]);
        }

        $this->Index();
    }

    public function Index(){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."mapcityimage");
        while($row= $qr->fetch_array())
            $this->mapCityImages[]= new MapCityImage($row);

        parent::Index("acp/mapCityImage");
    }
}