<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\Config;
use phpsgex\framework\models\LoggedUser;

abstract class BaseController{
    public $requireLogin= true, $requiredRank= 0, $view= null, $viewData= Array(),
            $path, $linkName, $user; /** @var LoggedUser $user */

    public function __construct() {
        $this->path= str_replace("phpsgex\\controllers\\", "", get_class($this));
        $this->linkName= str_replace( "Controller", "", get_class($this) );
        $this->user= Auth::Instance()->GetUser();
    }
    
    public function Index($view= null){
        $this->view= $view;

        $tmpdc= (new \ReflectionClass("phpsgex\\templates\\".Config::Instance()->template."\\TemplateDefinition"))->newInstance();
        $file= self::GetTemplatePath().$tmpdc->layout;
        if( !file_exists($file) )
            error_log($file." not found in path");
        require_once( $file );
    }

    public static function GetTemplatePath(){
        return __ROOT__."templates/".Config::Instance()->template."/";
    }

    public function Reload($act= ""){
        header( "Location: ?pg=".$this->GetLocation().
            ( strlen($act) > 0 ? "&act=$act" : "") );
    }

    public function GetLocation(){
        return str_replace("phpsgex\\controllers\\", "", get_class($this));
    }
}