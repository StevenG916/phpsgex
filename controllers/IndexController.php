<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\exceptions\LoginException;

class IndexController extends BaseController {
    public function __construct(){
        parent::__construct();
        $this->requireLogin= false;
    }

    public function Logout(){
        Auth::Instance()->Logout();
        return $this->Index();
    }

    public function Index(){
        if( isset($_POST["username"]) && $this->user == null ) {
            try {
                $this->user= Auth::Instance()->Login(CleanString($_POST["username"]), $_POST["password"]);
            } catch(LoginException $ex){
                $this->viewData["error"]= "Invalid login";
            }
        }

        if( $this->user == null )
            parent::Index("index/index");
        else (new CityController())->Index();
    }

    public function Register(){
        if( isset($_POST["register"]) && !multi_submit() ){
            $race= isset($_POST["race"]) ? (int)$_POST["race"] : 1;
            $initPos= isset($_POST["initPos"]) ? $_POST["initPos"] : null;
            $city_name= isset($_POST["city_name"]) ? CleanString($_POST["city_name"]) : "";
            try {
                Auth::Instance()->Register(CleanString($_POST["username"]), CleanString($_POST["email"]), $_POST["password"],
                    $race, CleanString($_POST["language"]), $city_name, $initPos);
            } catch(\Exception $ex){
                $this->viewData["error"]= $ex->getMessage();
                return $this->Index();
            }
            header("Location: ?pg=index");
        }

        parent::Index("index/register");
    }
}