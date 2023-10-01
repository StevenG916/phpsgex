<?php
namespace phpsgex\framework;

use phpsgex\controllers\ProfileController;
use phpsgex\framework\Auth;
use phpsgex\controllers\BaseController;
use phpsgex\controllers\IndexController;
use phpsgex\framework\models\Config;

final class Navigator{
    private static $pluginsControllers= [];
    
    public static function RegisterController(BaseController $c){
        if( array_key_exists($c->path, self::$pluginsControllers) )
            throw new \Exception("Controller path already exist!");
        
        self::$pluginsControllers[$c->path]= $c;
    }

    public static function GetPluginControllers($base= null){
        if( $base == null )
            return self::$pluginsControllers;

        $ret= [];
        foreach (self::$pluginsControllers as $pc) /** @var BaseController $pc */
            if( str_startswith( $pc->path, $base ) )
                $ret[]= $pc;

        return $ret;
    }
    
    public static function Navigate($strController, $strAction= "Index"){ 
        if( !file_exists(__ROOT__."controllers".DS.str_replace("\\", DS, $strController."Controller").".php")
            && !array_key_exists($strController, self::$pluginsControllers) ){
            error_log($strController."Controller not found");
            return (new IndexController())->Index();
        }

        $user= Auth::Instance()->GetUser();

        if( Config::Instance()->serverEnd != null && strtotime(Config::Instance()->serverEnd) >= time()
            && $user == null && $user->rank <= 1 ){
            $c= new ProfileController();
        } else if( array_key_exists($strController, self::$pluginsControllers) ){ //plugin controller
            $c= self::$pluginsControllers[$strController];
        } else {
            $ref= new \ReflectionClass("phpsgex\\controllers\\" . $strController . "Controller");
            $c= $ref->newInstance();
        } /** @var BaseController $c */

        try {
            if (!method_exists($c, $strAction)) {
                error_log("Method not found $strAction on " . get_class($c));
                return $c->Index();
            }

            if (!$c->requireLogin ||
                ($c->requireLogin && $user != null && $user->rank >= $c->requiredRank)) {
                return $c->{$strAction}();
            }
        } catch(\Exception $ex){
            error_log($ex->getTraceAsString());
        }

        return (new IndexController())->Index();
    }
}
