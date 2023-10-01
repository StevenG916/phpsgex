<?php
namespace phpsgex\framework\battle;

class BattleResult{
    const Win_Atk= 0, Win_Def= 1;
    public $winner,
        $atkUnits= array(), $defUnits= array(), $atkUnitsLost= array(), $defUnitsLost= array(),
        $city, $luck, $moral;
}