<?php
namespace phpsgex\framework\models;

class ResourceData {
	public $id, $name, $productionRate, $start, $icon;

    public static function GetAll(){
        $resdata = Array();
        global $DB;

        $qr= $DB->query("select id from ".TB_PREFIX."resdata");
        while( $row = $qr->fetch_array() ){
            $resdata[$row['id']] = new ResourceData($row['id']);
        }

        return $resdata;
    }

	public function __construct( $_resourceId ){
		global $DB;
		$this->id = $_resourceId;
		
		$info = $DB->query("SELECT * FROM ".TB_PREFIX."resdata WHERE id = $_resourceId")->fetch_array();
		$this->name = $info['name'];
		$this->productionRate = $info['prod_rate'];
		$this->start = $info['start'];
		$this->icon = $info['ico'];
	}
};

?>