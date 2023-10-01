<?php
namespace phpsgex\framework\models\ally;
use phpsgex\framework\Auth;

class AllyUserRole {
	public $id, $allyId, $userId, $chargeId, $nomeCarica;
	
	public static $_instance;
    public static function Instantiate( $id, Array $_row= null ){
    return call_user_func( self::$_instance, $id, $_row );
    }
	
	public function __construct( $_id, Array $row= null ){
	global $DB;
    if( $row == null ){
				
	#$qr= $DB->query("select * from ".TB_PREFIX."ally_user_role
    #where  userId= $_id" );
    #$row= $qr->fetch_array();

     }
		
		$this->id = $row["id"];
		$this->allyId = $row["allyId"];
		$this->userId = $row["userId"];
		$this->chargeId = $row["chargeId"];
		$this->nomeCarica = $row['nomeCarica'];
		
	}
	
	#funzione per recuperare il nome della carica dal player
	    public static function GetChargeById($id_name){
        global $DB;
		
		$qr= $DB->query("select role.*, charge.name as nomeCarica from ".TB_PREFIX."ally_user_role as role
		INNER JOIN ".TB_PREFIX."ally_charge as charge on charge.id=role.chargeId 
		where userId = '$id_name'  order by id asc");					 
						 
        #if( $qr->num_rows ==0 ) 
		#throw new \Exception("nessuna carica trovata");
        return self::Instantiate(0,$qr->fetch_array());
    }
	
	public static function GetAll(){
		global $DB;
		$qr= $DB->query("select role.*, charge.name as nomeCarica from ".TB_PREFIX."ally_user_role as role
		INNER JOIN ".TB_PREFIX."ally_charge as charge on charge.id=role.chargeId 
		where allyId = ".Auth::Instance()->GetUser()->allyId."  order by id asc");
		
        if(isset($_GET['id'])){
        if(is_numeric($_GET['id'])){

        $qr= $DB->query("select role.*, charge.name as nomeCarica from ".TB_PREFIX."ally_user_role as role
		INNER JOIN ".TB_PREFIX."ally_charge as charge on charge.id=role.chargeId
		where allyId = ". $_GET['id']."  order by id asc");
            }
        }

        $ret= Array();
		while($row= $qr->fetch_array())
		$ret[]= new AllyUserRole(0, $row);
		return $ret;
	}
};

	AllyUserRole::$_instance= function($id, Array $row= null){ return new AllyUserRole($id,$row); };