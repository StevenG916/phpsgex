<?php
namespace phpsgex\framework\models\units;

class UnitQueue{
    public $id, $unit, $build, $number, $endTime;

    /**
     * @param $id
     * @param array|null $row
     * @param UnitData|null $unitData
     * @return UnitQueue
     * @throws \Exception
     */
    public static function Instantiate($id, array $row= null, UnitData $unitData= null){
        if($row==null){
            if($id==null) throw new \Exception("id is null");

            global $DB;
            $qr= $DB->query("select * from ".TB_PREFIX."unit_que 
                                    where id= $id");

            if( $qr->num_rows ==0 ) throw new \Exception("invalid id: $id");

            $row= $qr->fetch_array();
        }

        if($unitData==null)
            $unitData= new UnitData($row["id_unt"]);

        return new UnitQueue( $row["id"], $unitData, $row["uqnt"], $row["end"] );
    }

    public function __construct( $id, UnitData $unit, $number, $endTime ){ //TODO refactor
        $this->id= $id;
        $this->unit= $unit;
        $this->number= $number;
        $this->endTime= $endTime;
        $this->build= $unit->build;
    }
}