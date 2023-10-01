<?php
namespace phpsgex\framework\models\ally;
use phpsgex\framework\Auth;

class AllyPact {
	public $ally1, $ally2, $type, $status, $data_start, $data_end;
	
		public function __construct( $_id, Array $row= null ){
		global $DB;
        if( $row == null ){
            $qr= $DB->query("select * from ".TB_PREFIX."ally_pact
            where  ally1 = ".$this->user->allyId."" );
							 
            $row= $qr->fetch_array();

		}
		
		$this->ally1 = $row["ally1"];
		$this->ally2 = $row["ally2"];
		$this->type = $row["type"];
		$this->status = $row["status"];
		$this->data_start = $row["data_start"];
		$this->data_end = $row["data_end"];
		
	}


		public static function GetAll(){
		global $DB;
		$qr= $DB->query("select * from ".TB_PREFIX."ally_pact
            where ally1 = ".$this->user->allyId."  ");
		$ret= Array();
		while($row= $qr->fetch_array())
		$ret[]= new AllyPact(0, $row);
		return $ret;
	}
		
};



?>