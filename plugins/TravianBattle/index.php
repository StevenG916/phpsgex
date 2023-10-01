<?php
namespace phpsgex\plugins\travianBattle;

use phpsgex\includes\Plugin;
use phpsgex\framework\BattleSystem;

class TravianBattle extends Plugin{
    public function __construct(){
        parent::__construct(null);
    }
    
    public function Activate(){
        BattleSystem::$_instance= new TravianBattleSystem();
    }
}