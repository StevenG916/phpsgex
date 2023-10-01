<?php
namespace phpsgex\plugins\travianBattle;

use phpsgex\framework\models\City;
use phpsgex\framework\models\units\Troop;
use phpsgex\framework\BattleSystem;

class TravianBattleSystem extends BattleSystem{
    public function SufferAttack(City $attackedCity){
        global $DB;
        $qr= $DB->query("SELECT owner_id, `to`, id_unt, id, uqnt FROM units WHERE `to`= {$attackedCity->id} AND `time` <= ".time()." GROUP BY owner_id, `to`, id_unt, id, uqnt ORDER BY owner_id");
        $atkUnt= array();
        $prevOwn= null;
        while($row= $qr->fetch_array()){
            $u= new Troop($row['id'], $row['uqnt'], $row['id_unt']);
            if( $prevOwn== null ) $prevOwn= $row["owner_id"];
            if( $prevOwn== $row["owner_id"] )
                $atkUnt[]= $u;
            else {
                $this->CalculateBattle($prevOwn, $atkUnt, $attackedCity);
                $prevOwn= $row["owner_id"];
                $atkUnt= array();
                $atkUnt[]= $u;
            }
        }
    }

    private function luk(){
        return 1;
    }

    public function CalculateBattle($attacker, Array $attackingUnits, City $defenderCity){
        Troop::SortUnitsBySpeed( $attackingUnits );
        $defenderUnits= $defenderCity->GetTroops(); /* @var array $defenderUnits */
        Troop::SortUnitsBySpeed( $defenderUnits );

        $svAtkUnts= array_clone($attackingUnits); $svDefUnts= array_clone($defenderUnits);

        $attackerWin= $finished= false;
        if( count($defenderUnits) == 0 ) $attackerWin= $finished= true;

        $atkUntHealth= array();
        for($i=0; $i < count($attackingUnits); $i++)
            $atkUntHealth[ $attackingUnits[$i]->id ]= $attackingUnits[$i]->unit->health * $attackingUnits[$i]->unit->quantity;
        $defUntHealth= array();
        for($i=0; $i < count($attackingUnits); $i++)
            $defUntHealth[ $defenderUnits[$i]->id ]= $defenderUnits[$i]->unit->health * $defenderUnits[$i]->unit->quantity;

        $maxCycles= max( count($attackingUnits), count($defenderUnits) );
        $i= 0;
        while( !$finished && $i < $maxCycles ){
            $atkUnt= $attackingUnits[$i]; /* @var Troop $atkUnt */
            $defUnt= $defenderUnits[$i];  /* @var Troop $defUnt */

            //damage
            $defUntHealth[ $defUnt->id ] -= $atkUnt->unit->attack * $atkUnt->quantity * $this->luk();
            $atkUntHealth[ $atkUnt->id ] -= $defUnt->unit->attack * $defUnt->quantity * $this->luk();

            //update quantity
            $defUnt->quantity= ($defUntHealth[ $defUnt->id ] < 0.1) ? 0 : ceil( $defUntHealth[ $defUnt->id ] / $defUnt->quantity );
            $atkUnt->quantity= ($atkUntHealth[ $atkUnt->id ] < 0.1) ? 0 : ceil( $atkUntHealth[ $atkUnt->id ] / $atkUnt->quantity );

            if( $defUnt->quantity == 0 ){

            }

            $i++;
        }
    }
}