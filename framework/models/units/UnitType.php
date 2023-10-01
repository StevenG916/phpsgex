<?php
namespace phpsgex\framework\models\units;

final class UnitType{
    const Foot= 0, Archer= 1, Horse= 2, Machine= 3, Spy= 4, Hero= 5, Ram= 6, Noble= 7;

    public static function Values(){
        $type= new \ReflectionClass( __CLASS__ );
        return $type->getConstants();
    }
}