<?php
namespace phpsgex\framework\models\ally;
use phpsgex\framework\Auth;

class AllyCharge {
	public $id, $id_ally, $name, $img, $adm_full, $adm_recruit, $adm_wars, $adm_reliable, $adm_forum;
	protected $desc;
	
	public function __construct( $_id, Array $row= null ){
		#if($_id <= 0 && $row == null)
		#throw new \Exception("invalid id");

		global $DB;
        if( $row == null ){
            $qr= $DB->query("select * from ".TB_PREFIX."ally_charge 
                where id_ally = ".$this->user->id );
            $row= $qr->fetch_array();
		}
		
		$this->id = $row["id"];
		$this->id_ally = $row["id_ally"];
		$this->name = $row["name"];
		$this->desc = $row["desc"];
		$this->img = $row["img"];
		$this->adm_full = $row["adm_full"];
		$this->adm_recruit = $row["adm_recruit"];
		$this->adm_wars = $row["adm_wars"];
		$this->adm_reliable = $row["adm_reliable"];
		$this->adm_forum = $row["adm_forum"];
	}

	public function GetDescription(){
		return htmlspecialchars_decode($this->desc);
	}
	
	public static function GetAll(){
        global $DB;
        $qr= $DB->query("select * from " . TB_PREFIX . "ally_charge 
            where id_ally = ".Auth::Instance()->GetUser()->allyId." order by id asc");

        if(isset($_GET['id'])){
            if(is_numeric($_GET['id'])){
                $qr= $DB->query("select * from " . TB_PREFIX . "ally_charge 
                    where id_ally = {$_GET['id']} order by id asc");
            }

        }

        $ret= Array();
        while($row= $qr->fetch_array())
        $ret[]= new AllyCharge(0, $row);
        return $ret;
	}
}