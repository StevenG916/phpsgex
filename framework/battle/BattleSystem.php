<?php
namespace phpsgex\framework\battle;

use phpsgex\framework\models\User;
use phpsgex\framework\models\City;
use phpsgex\framework\models\units\Troop;
/**
 * Static Class BattleSystem
 * must extend this class if you want to make your own BattleSystem
 */
class BattleSystem{
    public static $_instance;

    /**
     * @return BattleSystem
     */
    public static function Instance(){
        return self::$_instance;
    }

    /*public function SufferAttack(City $attackedCity){
        global $DB;
        //search ataking units
        $qratkunt= $DB->query("SELECT ".TB_PREFIX."units.*, ".TB_PREFIX."t_unt.vel FROM ".TB_PREFIX."units JOIN ".TB_PREFIX."t_unt ON (".TB_PREFIX."units.id_unt = ".TB_PREFIX."t_unt.id) WHERE `to`= {$attackedCity->id} AND `time` <=".time()." AND `action` =1 ORDER BY `".TB_PREFIX."t_unt`.`vel` DESC");
        if( $qratkunt->num_rows ==0 ) return; //if there isn't an atack end

        //your units (or supports)
        $ayu= $attackedCity->GetUnits();
        if( count( $ayu ) > 0 ){ //battle begin, create an array of your units
            //atacking units
            $attackingUnits= array();
            $attakerowner=0;
            $atk_from = 0;
            while( $aatu= $qratkunt->fetch_array() ){
                $attakerowner= $aatu['owner_id'];
                $atk_from = $aatu['from'];
                $attackingUnits[]= new Unit( $aatu['id'], $aatu['uqnt'], $aatu['id_unt'] );
            }

            // duel
            $yi=0;
            $ai=0;
            $maxcicles = 1000;
            $cicle = 0;
            while( $yi < count($ayu) && $ai < count($attackingUnits) && $cicle++ < $maxcicles ){
                $savayu= clone $ayu[$yi];
                $ayu[$yi]->GetAttackedBy( $attackingUnits[$ai] );
                if( $ayu[$yi]->quantity <= 0 ) $yi++;

                $attackingUnits[$ai]->GetAttackedBy( $savayu );
                if( $attackingUnits[$ai]->quantity <= 0 ) $ai++;
            }

            // update units
            //your untis
            for($i=0; $i < count($ayu); $i++){
                $DB->query("UPDATE `".TB_PREFIX."units` SET `uqnt`= {$ayu[$i]->quantity} WHERE `id`= {$ayu[$i]->id} LIMIT 1 ;");
            }

            $attackerCity= City::Instantiate( (int)$atk_from );
            $travelTime= $attackedCity->mapPosition->GetDistance( $attackerCity->mapPosition );

            for($i=0; $i < count($attackingUnits); $i++){
                $DB->query("UPDATE `".TB_PREFIX."units` SET `uqnt`= {$attackingUnits[$i]->quantity},`from`= {$attackedCity->id} ,`to`= $atk_from ,`where`= NULL,`time` = ".( time() +(int)$travelTime )." ,`action` = '0' WHERE `id`= {$attackingUnits[$i]->id} LIMIT 1 ;");
            }
            // clear units where uqnt=0
            $DB->query("delete from units where uqnt <= 0");
        } else { //there are no units so enemy win
            while( $riga= $qratkunt->fetch_array() ){
                $attakerowner= $riga['owner_id'];
                if( $riga['time']<= time() )
                    $DB->query("UPDATE `".TB_PREFIX."units` SET `from`= {$riga['to']} ,`to`= {$riga['from']} ,`where`= NULL ,`time`= 0 ,`action`= '0' WHERE `id`= {$riga['id']} LIMIT 1;");
            }
        }

        //send battle report
        $userAttacker= new User( (int)$attakerowner );
        $m1= "You were attacked form <a href='?pg=profile&usr={$userAttacker->id}'>{$userAttacker->name}</a> !";
        SendMessage( null, $attackedCity->user->id, "Battle report", CleanString($m1), 2 );

        $m2= "Attack to <a href='?pg=profile&usr={$attackedCity->user->id}'>{$attackedCity->user->name}</a> complete!";
        SendMessage( null, $userAttacker->id, "Battle report", CleanString($m2), 2 );
    }*/

    /**
     * @param array $atkUnits
     * @param array $defUnits
     * @return BattleResult
     */
    public function Battle(Array $atkUnits, Array $defUnits, City $city){ //TODO
        $ret= new BattleResult();
        $ret->atkUnits= $atkUnits;
        $ret->defUnits= $defUnits;
        $ret->winner= BattleResult::Win_Def;

        return $ret;
    }

    /**
     * @param array $units
     * @return int
     */
    public static function CountTotalUnits( Array $units ){
        $c= 0;
        foreach($units as $unit)
            $c += $unit->quantity;
        return $c;
    }

    protected function StealResources(Array $units){
        //TODO
    }
}

BattleSystem::$_instance= new BattleSystem();
