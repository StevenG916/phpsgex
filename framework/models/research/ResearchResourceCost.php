<?php
namespace phpsgex\framework\models\research;

use phpsgex\framework\models\ModelLoader;

class ResearchResourceCost extends ModelLoader {
    public $research, $resource, $cost, $moltiplier;
    
    public static function GetCosts($researchId){
        global $DB;
        $ret= array();
        
        $qr= $DB->query("select res.id as resource, costs.research, costs.cost, costs.moltiplier "
            . "from ".TB_PREFIX."resdata as res left join "
                . "(select * from ".TB_PREFIX."t_research_resourcecost where research= $researchId) as costs on( res.id = resource )");
        
        while( $row= $qr->fetch_array() ){
            $ret[(int)$row["resource"]]= new ResearchResourceCost($row);
        }
        
        return $ret;
    }
    
    public function __construct(array $values) {
        parent::__construct($values);
    }
}