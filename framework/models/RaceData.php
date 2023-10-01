<?php
namespace phpsgex\framework\models;

class RaceData {
	public $id, $name, $image;
	protected $description;
	
	public function __construct( $_id, Array $row= null ){
		if( $row == null ) {
			global $DB;
			$query = $DB->query("select * from " . TB_PREFIX . "races where id = $_id");
			if ($query->num_rows == 0) throw new Exception("Race $_id doesn't exist!");
			$row = $query->fetch_array();
		}
		
		$this->id = $row["id"];
		$this->name = $row["rname"];
		$this->description = $row["rdesc"];
		$this->image = $row["img"];
	}

	public function GetDescription(){
		return htmlspecialchars_decode($this->description);
	}

	public static function GetAll(){
		global $DB;
		$qr= $DB->query("select * from ".TB_PREFIX."races order by id asc");
		$ret= Array();
		while($row= $qr->fetch_array())
			$ret[]= new RaceData(0, $row);
		return $ret;
	}
};

?>