<?php
namespace phpsgex\framework\models\ally;
use phpsgex\framework\models\User;

class Ally {
	public $id, $name, $description, $ownerId, $inviteText, $access;
	
	public static $_instance;
    public static function Instantiate( $id, Array $_row= null ){
        return call_user_func( self::$_instance, $id, $_row );
    }

	public function __construct( $_id, Array $row= null ){
	    global $DB;
        if( $row == null ){
            $qr= $DB->query("select * from ".TB_PREFIX."ally
                             where id= $_id");
            $row= $qr->fetch_array();
        }

        $this->id= $row["id"];
        $this->name= $row["name"];
        $this->description= $row["desc"];
        $this->ownerId= $row["owner"];
        $this->inviteText= $row['invite_text'];
	}
	
    public static function GetByName($name){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."ally
                             where name = '$name'");
        if( $qr->num_rows ==0 ) throw new \Exception("alleanza non trovata");
        return self::Instantiate(0,$qr->fetch_array());
    }

	//returns a list of Users that are member of the ally
	public function GetMembers(){
	    global $DB;
        $qr= $DB->query("select id from ".TB_PREFIX."users where ally_id= ".$this->id);
        $users= Array();
        while( $row= $qr->fetch_array() ){
            $users[] = User::Instantiate( $row["id"] );
        }
        return $users;
	}
	
	//calculate points
	public function GetPoints(){
	    global $DB;
        $qr= $DB->query("SELECT SUM(points) AS result FROM ".TB_PREFIX."users WHERE ally_id = ".$this->id);
        $row= $qr->fetch_array();
        return $row['result'];
	}
	
	#estraè tutte le alleanza
    public static function GetAllyList(){
	    global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."ally");
		
		$ret= Array();
		while( $row= $qr->fetch_array() )
		$ret[]= Ally::Instantiate(0, $row);
		return $ret;
	}
	
	//returns a list of AllyPact
	public function GetPacts(){
	
	}
};
Ally::$_instance= function($id, Array $row= null){ return new Ally($id,$row); };

?>