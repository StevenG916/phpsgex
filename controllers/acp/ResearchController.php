<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class ResearchController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Delete(){
        if( isset($_GET["id"]) ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."t_research where id= ".(int)$_GET["id"]);
        }
        
        $this->Index();
    }
    
    public function Create(){
        if( count($_POST) >0 ){
            global $DB;
            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $desc= htmlspecialchars($_POST["description"]);

            $qr= "insert into ".TB_PREFIX."t_research
                values(null, '{$_POST["name"]}', '$desc', $race, '{$_POST["image"]}',
                    {$_POST["time"]}, {$_POST["timeMul"]}, {$_POST["points"]}, {$_POST["maxLevel"]})";

            echo $qr;

            $DB->query($qr);
            $researchId= $DB->insert_id;

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("insert into ".TB_PREFIX."t_research_resourcecost
                            values($researchId, $resId, $cost, {$_POST["costMul"][$resId]})");
            }
        }

        $this->Reload();
    }

    public function Edit(){
        if( count($_POST) >0 /*&& !multi_submit()*/ ){
            global $DB;
            $researchId= (int)$_POST["id"];

            $race= (int)$_POST["race"]; if($race==0) $race= "null";
            $desc= htmlspecialchars($_POST["description"]);;

            $qr= "update ".TB_PREFIX."t_research
                set name= '{$_POST["name"]}', `desc`= '$desc', arac= $race, img= '{$_POST["image"]}',
                time= {$_POST["time"]}, time_mpl= '{$_POST["timeMul"]}', gpoints= {$_POST["points"]},
                maxlev= {$_POST["maxLevel"]}
                where id= $researchId";

            $DB->query($qr);

            foreach( $_POST["cost"] as $resId => $cost ){
                $DB->query("replace into ".TB_PREFIX."t_research_resourcecost
                            values($researchId, $resId, $cost, {$_POST["costMul"][$resId]})");
            }

            //requisites
            $DB->query("delete from ".TB_PREFIX."t_research_reqbuild
                        where research= $researchId");
            foreach( $_POST["buildRequisite"] as $key => $rbid ){
                $lev= $_POST["buildRequisiteLevel"][$key];
                if($lev <= 0) continue;
                $DB->query("insert into ".TB_PREFIX."t_research_reqbuild values
                            ($researchId, $rbid, $lev)");
            }

            $DB->query("delete from ".TB_PREFIX."t_research_req_research
                        where research= $researchId");
            foreach( $_POST["researchRequisite"] as $key => $rbid ){
                $lev= $_POST["researchRequisiteLevel"][$key];
                if($lev <= 0) continue;
                $DB->query("insert into ".TB_PREFIX."t_research_req_research values
                            ($researchId, $rbid, $lev)");
            }
        }

        $this->Reload();
    }
    
    public function Index(){
        parent::Index("acp/research");
    }
}