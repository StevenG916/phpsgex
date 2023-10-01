<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\ally\Ally;
use phpsgex\framework\models\User;

class ProfileController extends BaseController {
    public $ally;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin= false;
    }

    public function HighScores(){
        global $DB;

        $users= Array();
        $qr= $DB->query("select * from ".TB_PREFIX."users order by points desc");
        while( $row= $qr->fetch_array() )
            $users[]= User::Instantiate(null, $row);

        $this->viewData["users"]= $users;

        parent::Index("highscores");
    }

    public function GetSearchName(){
        if( !isset($_GET["name"]) ){
            echo json_encode( array() );
            return;
        }

        $name= $_GET["name"];
        global $DB;
        $qr= $DB->query("select username from ".TB_PREFIX."users where username like '%$name%'");
        $ret= array();

        while($row= $qr->fetch_array())
            $ret[]= $row["username"];

        echo json_encode($ret);
    }

    public function Edit(){
        if( count($_POST) >0 && !multi_submit() ){
            if( isset($_FILES['image']) && $_FILES['image']['error'] ==0 ) {
                $temp= $_FILES['image']['tmp_name'];
                list($width, $height, $type)= getimagesize($temp);
                if( $width > 256 && $height > 256 )
                    $this->viewData["error"]= "Image too large";
                else if( $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG )
                    $this->viewData["error"]= "Invalid image format! Only .jpg or .png";
                else {
                    $picName= $this->user->id.($type == IMAGETYPE_JPEG ? ".jpg" : ".png");
                    move_uploaded_file($temp, __ROOT__."uploads/profile/i$picName");
                    echo __ROOT__."uploads/profile/i$picName";
                }
            }

            global $DB;
            $DB->query("update ".TB_PREFIX."users
                        set lang= '{$_POST["language"]}'
                        where id= ".$this->user->id);

            $this->Index();
        } else parent::Index("profile/edit");
    }

    public function Index(){
        if( isset($_GET["id"]) ){
            try {
                $this->user = User::Instantiate( (int)$_GET["id"] );
            } catch(\Exception $ex){
                $this->viewData["error"]= "Invalid userId";
            }
        } //else $this->user= $this->user;

        if($this->user == null) return $this->HighScores();

        $this->ally= $this->user->allyId != null ? new Ally( $this->user->allyId ) : null;

        parent::Index("profile/index");
    }
}