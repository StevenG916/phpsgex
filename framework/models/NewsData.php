<?php
namespace phpsgex\framework\models;

class NewsData {
	public $id, $title, $link, $graphic, $datatime;
	protected $text;
	
	public function __construct( $_id, Array $row= null ){
		if( $row == null ) {
			global $DB;
			$query = $DB->query("select * from " . TB_PREFIX . "news where id = $_id");
			if ($query->num_rows == 0) throw new Exception("News $_id doesn't exist!");
			$row = $query->fetch_array();
		}
		
		$this->id = $row["id"];
		$this->title = $row["title"];
		$this->text = $row["text"];
		$this->link = $row["link"];
		$this->graphic = $row["graphic"];
		$this->datetime = $row["datetime"];
	}

	public function GetDescription(){
		return htmlspecialchars_decode($this->text);
	}

	public static function GetAll(){
		global $DB;
		$qr= $DB->query("select * from ".TB_PREFIX."news order by id asc");
		$ret= Array();
		while($row= $qr->fetch_array())
			$ret[]= new NewsData(0, $row);
		return $ret;
	}
};

?>