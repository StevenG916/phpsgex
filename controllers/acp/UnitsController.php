<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class UnitsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Delete(){
        if( isset($_GET["id"]) ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."t_unt where id= ".(int)$_GET["id"]);
        }
        
        $this->Index();
    }
    
    public function Create(){
        if( !empty($_POST) && !multi_submit() ){
            global $DB;
            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $desc= htmlspecialchars($_POST["description"]);

            $qr= "insert into ".TB_PREFIX."t_unt
                values(null, '{$_POST["name"]}', $race, '{$_POST["image"]}', {$_POST["health"]}, {$_POST["attack"]},
                    {$_POST["defence"]}, {$_POST["speed"]}, {$_POST["carry"]}, {$_POST["time"]}, '$desc', {$_POST["type"]}, {$_POST["build"]})";

            echo $qr;

            $DB->query($qr);
            $researchId= $DB->insert_id;

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("insert into ".TB_PREFIX."t_unt_resourcecost
                            values($researchId, $resId, $cost, {$_POST["costMul"][$resId]})");
            }
        }

        $this->Index();
    }

    public function Edit(){
        if( !empty($_POST) /*&& !multi_submit()*/ ){
            global $DB;
            $unitId= (int)$_POST["id"];

            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $desc= $DB->escape_string($_POST["description"]);;

            $qr= "update ".TB_PREFIX."t_unt
                set name= '{$_POST["name"]}', `desc`= '$desc', race= $race, img= '{$_POST["image"]}', etime= {$_POST["time"]},
                health= {$_POST["health"]}, atk= {$_POST["attack"]}, def= {$_POST["defence"]}, vel= {$_POST["speed"]},
                res_car_cap= {$_POST["carry"]}, build= {$_POST["build"]}, type= {$_POST["type"]}, def_horse= {$_POST["defenceHorse"]},
                def_archer= {$_POST["defenceArcher"]}
                where id= $unitId";

            $DB->query($qr);

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("replace into ".TB_PREFIX."t_unt_resourcecost values
                            ($unitId, $resId, $cost)");
            }

            //requisites
            $DB->query("delete from ".TB_PREFIX."t_unt_reqbuild
                        where unit= $unitId");

            if(isset($_POST["buildRequisite"]))
                foreach( $_POST["buildRequisite"] as $key => $rbid ){
                    $lev= $_POST["buildRequisiteLevel"][$key];
                    if($lev <= 0) continue;
                    $DB->query("insert into ".TB_PREFIX."t_unt_reqbuild values
                                ($unitId, $rbid, $lev)");
                }

            $DB->query("delete from ".TB_PREFIX."t_unt_req_research
                        where unit= $unitId");

            if(isset($_POST["researchRequisite"]))
                foreach( $_POST["researchRequisite"] as $key => $rbid ){
                    $lev= $_POST["researchRequisiteLevel"][$key];
                    if($lev <= 0) continue;
                    $DB->query("insert into ".TB_PREFIX."t_unt_req_research values
                                ($unitId, $rbid, $lev)");
                }
        }

        $this->Index();
    }
    
    public function Index(){
        parent::Index("acp/units");
    }
}