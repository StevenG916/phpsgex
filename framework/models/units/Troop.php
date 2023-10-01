<?php
namespace phpsgex\framework\models\units;

/**
 * group of units
 * Class Troop
 * @package phpsgex\framework\models\troop
 */
class Troop { //unit data (used only for battle)
    /** @var UnitData $unit */
    public $id, $quantity, $ownerId, $from, $to, $where, $time, $action, $unit;

    public static $_instance;

    /**
     * @param $_id
     * @param array|null $row
     * @return Troop
     */
    public static function Instantiate($_id, Array $row= null){
        return call_user_func(self::$_instance, $_id, $row);
    }

    public function __construct( $_id= 0, Array $row= null ){
        if($row==null){
            if($_id == 0) return;
            global $DB;
            $qr= $DB->query("select * from ".TB_PREFIX."units
                              where id= $_id");

            if($qr->num_rows == 0) throw new \Exception("Troop not found with id= $_id");

            $row= $qr->fetch_array();
        }

        $this->id= $row["id"];
        $this->id_unt= $row["id_unt"];
        $this->quantity= $row["uqnt"];
        $this->ownerId= $row["owner_id"];
        $this->from= $row["from"];
        $this->to= $row["to"];
        $this->where= $row["where"];
        $this->startTime= $row["startTime"];
        $this->time= $row["time"];
        $this->action= $row["action"];

        $this->unit= UnitData::Instantiate( $row["id_unt"] );
    }

    public static function SortUnitsBySpeed( Array $units ){
        usort( $units, function(Troop $a, Troop $b){ return $a->unit->speed >= $b->unit->speed; } );
    }
}

Troop::$_instance= function($_id, Array $row= null ){ return new Troop($_id, $row); };