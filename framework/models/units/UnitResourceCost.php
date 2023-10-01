<?php
namespace phpsgex\framework\models\units;

use phpsgex\framework\models\ModelLoader;

class UnitResourceCost extends ModelLoader {
    public $unit, $resource, $cost= 0;
    
    public static function GetCosts($unitId){
        global $DB;
        $ret= array();

        $qr= $DB->query("select res.id as resource, costs.unit, costs.cost "
            . "from ".TB_PREFIX."resdata as res left join "
            . "(select * from ".TB_PREFIX."t_unt_resourcecost 
                where unit= $unitId) as costs on( res.id = resource )");
        
        while( $row= $qr->fetch_array() ){
            $ret[(int)$row["resource"]]= new UnitResourceCost($row);
        }
        
        return $ret;
    }
    
    public function __construct(array $values) {
        parent::__construct($values);
    }
}
