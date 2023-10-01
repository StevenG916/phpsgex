<?php
namespace phpsgex\framework\battle;

use phpsgex\framework\models\User;
use phpsgex\framework\MessageType;

class BattleReportManager{
    public static $_instance;

    /**
     * @return BattleReportManager
     */
    public static function Instance(){
        return self::$_instance;
    }

    public function GenerateReport(BattleResult $br){
        $atkrBodyUnits= "<table><tr><th>Unità</th><th>Iniziali</th><th>Perdite</th></tr>";

        foreach( $br->atkUnits as $i => $unit ){ // Unit $unit 
            $atkrBodyUnits.="<tr><td>{$unit->unit->name}</td><td>{$unit->quantity}</td><td>".(
                array_key_exists($i, $br->atkUnitsLost) ? $br->atkUnitsLost[$i] : 0)."</td></tr>";
        }

        $atkrBodyUnits.="</table>";

        $defBodyUnits= "<table><tr><th>Unità</th><th>Iniziali</th><th>Perdite</th></tr>";

        foreach( $br->defUnits as $i => $unit ){ // Unit $unit 
            $defBodyUnits.="<tr><td>{$unit->unit->name}</td><td>{$unit->quantity}</td><td>".(
               array_key_exists($i, $br->defUnitsLost) ? $br->defUnitsLost[$i] : 0)."</td></tr>";
        }

        $defBodyUnits.="</table>";

        $footer= "<div>Fortuna: {$br->luck}%<br>Morale: {$br->moral}%</div>";

        $arr_br = (array) ($br);
        

        //attacker report
        $body= "<h3>Hai ".( $br->winner == BattleResult::Win_Atk ? "vinto" : "perso" )."</h3><br>Tue unità $atkrBodyUnits <br> Difesa $defBodyUnits $footer";
        $atacker= User::Instantiate($br->atkUnits[0]->ownerId);
        $append_arr =array(
            'status'=>'attack'
        );
        $combined = array_merge($arr_br, $append_arr);
        $encoded = json_encode($combined);

        SendMessage(null, $atacker->id, "Attack to ".$br->city->name, $encoded, MessageType::Report);


        //defence report
        if( $br->city->user != null ) {
            $body = "<h3>Hai" . ($br->winner == BattleResult::Win_Def ? "vinto" : "perso") . "</h3><br>Tue unità $defBodyUnits <br> Attacco $atkrBodyUnits $footer";
            $append_arr =array(
                'status'=>'defense'
            );
            $combined = array_merge($arr_br, $append_arr);
            $encoded = json_encode($combined);
    
            SendMessage(null, $br->city->user->id, "Attacked by " . $atacker->name, $encoded, MessageType::Report);
        }

		
    }
}

BattleReportManager::$_instance= new BattleReportManager();