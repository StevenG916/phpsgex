<?php
namespace phpsgex\framework\models\buildings;

use phpsgex\framework\models\ModelLoader;

class BuildResourceCost extends ModelLoader {
    public $build, $resource, $cost= 0, $moltiplier= 0;
    
    public static function GetCosts($buildId){
        global $DB;
        $ret= array();
        
        $qr= $DB->query("select res.id as resource, costs.build, costs.cost, costs.moltiplier "
            . "from ".TB_PREFIX."resdata as res left join "
            . "(select * from ".TB_PREFIX."t_build_resourcecost where build= $buildId) as costs on( res.id = resource )");
        
        while( $row= $qr->fetch_array() ){
            $ret[(int)$row["resource"]]= new BuildResourceCost($row);
        }
        
        return $ret;
    }
    
    public function __construct(array $values) {
        parent::__construct($values);
    }
}