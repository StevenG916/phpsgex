<?php
namespace phpsgex\framework;

use phpsgex\framework\models\Config;

final class Helper {
    /**
     * @param $buildQueue array build queue
     * @return bool
     */
    public static function IsBuildQueueFull(Array $buildQueue){
        $config= Config::Instance();
        return $config->build_que_max != 0 && count($buildQueue) >= $config->build_que_max;
    }
}