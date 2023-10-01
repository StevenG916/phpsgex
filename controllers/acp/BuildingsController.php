<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class BuildingsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Delete(){
        if( isset($_GET["id"]) ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."t_builds where id= ".(int)$_GET["id"]);
        }
        
        $this->Index();
    }
    
    public function Create(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $func= $_POST["function"]; $func= ($func == "none") ? "null" : "'$func'";
            $produce= (int)$_POST["produce"]; if($produce==0) $produce= "null";
            $desc= htmlspecialchars($_POST["description"]);
            $imgtags= $DB->escape_string($_POST["imgtags"]);
            $opthtml= $DB->escape_string($_POST["opthtml"]);

            $qr= "insert into ".TB_PREFIX."t_builds
                values(null, $race, '{$_POST["name"]}', $func, $produce, '{$_POST["image"]}',
                    '$desc', {$_POST["time"]}, {$_POST["timeMul"]}, {$_POST["points"]}, {$_POST["pointsMul"]},
                    {$_POST["maxLevel"]}, '$imgtags', '$opthtml')";

            $DB->query($qr);
            $buildId= $DB->insert_id;

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("insert into ".TB_PREFIX."t_build_resourcecost
                            values($buildId, $resId, $cost, {$_POST["costMul"][$resId]})");
            }
        }

        $this->Reload();
    }

    public function Edit(){
        if( count($_POST) >0 /*&& !multi_submit()*/ ){
            global $DB;
            $buildId= (int)$_POST["id"];

            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $func= $_POST["function"]; $func= ($func == "none") ? "null" : "'$func'";
            $produce= (int)$_POST["produce"]; if($produce==0) $produce= "null";
            $desc= htmlspecialchars($_POST["description"]);
            $imgtags= $DB->escape_string($_POST["imgtags"]);
            $opthtml= $DB->escape_string($_POST["opthtml"]);

            $qr= "update ".TB_PREFIX."t_builds
                set arac= $race, name= '{$_POST["name"]}', func= $func, produceres= $produce, img= '{$_POST["image"]}',
                `desc`= '$desc', time= {$_POST["time"]}, time_mpl= {$_POST["timeMul"]}, gpoints= {$_POST["points"]},
                pointmul= {$_POST["pointsMul"]}, maxlev= {$_POST["maxLevel"]}, imgtags= '$imgtags', opthtml= '$opthtml'
                where id= $buildId";

            $DB->query($qr);

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("replace into ".TB_PREFIX."t_build_resourcecost
                            values($buildId, $resId, $cost, {$_POST["costMul"][$resId]})");
            }

            //requisites
            $DB->query("delete from ".TB_PREFIX."t_build_reqbuild
                        where build= $buildId");
            foreach( $_POST["buildRequisite"] as $key => $rbid ){
                $lev= $_POST["buildRequisiteLevel"][$key];
                if($lev <= 0) continue;
                $DB->query("insert into ".TB_PREFIX."t_build_reqbuild values
                            ($buildId, $rbid, $lev)");
            }

            $DB->query("delete from ".TB_PREFIX."t_build_req_research
                        where build= $buildId");
            foreach( $_POST["researchRequisite"] as $key => $rbid ){
                $lev= $_POST["researchRequisiteLevel"][$key];
                if($lev <= 0) continue;
                $DB->query("insert into ".TB_PREFIX."t_build_req_research values
                            ($buildId, $rbid, $lev)");
            }
        }

        $this->Reload();
    }
    
    public function Index(){
        parent::Index("acp/buildings");
    }
}