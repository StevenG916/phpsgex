<?php
namespace phpsgex\framework\models\research;

use phpsgex\framework\MapCoordinates;
use phpsgex\framework\Auth;


class ResearchVillage{
	public static $_instance;
	
	public static function search__($a){	//Search village in base from coordinate, name village or username;
		$if_coord = str_getcsv($a['input_val'], "|", "\"");
		$arr = array();

		if(!isset($if_coord[1])){ 
			if($_POST['trg_type'] == "player_name"){
				$q = "select A.username, A.points, B.id, B.x, B.y, B.name, B.owner from ".TB_PREFIX."users A, ".TB_PREFIX."city B where A.capcity=B.id and A.username like '%".$if_coord[0]."%' limit 5";
			}else if($_POST['trg_type'] == "village_name"){
				$q = "select A.username, A.points, B.id, B.x, B.y, B.name, B.owner from ".TB_PREFIX."users A, ".TB_PREFIX."city B where A.id=B.owner and B.name like '%".$if_coord[0]."%' limit 5";
			}
		}else{
			$q = "select * from ".TB_PREFIX."city where x='".$if_coord[0]."' and y='".$if_coord[1]."'";
		}
		
		try{
			global $DB;
			$query = $DB->query($q);

			while($row= $query->fetch_array()){
				$arr[] = $row;//array_push(, $row);
				//print_r($row);
			} 
			
			echo json_encode($arr);
		} catch (Exception $e) {
			die("Oh noes! There's an error in the query: ". $e);
		}
		
	}
	
}
