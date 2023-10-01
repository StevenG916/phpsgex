<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;;

class NewsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
   }

    public function Index(){
        parent::Index("acp/news");
    }

    public function Delete(){
        if( isset($_GET["id"]) && $_GET["id"] != 1 ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."news
                        where id= ".(int)$_GET["id"]);
        }
        $this->Index();
    }

    public function Create(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            $DB->query("insert into ".TB_PREFIX."news values
	  (null, '{$_POST["title"]}', '".$DB->escape_string($_POST["text"])."', '{$_POST["link"]}', '{$_POST["graphic"]}', NOW())");
							
        }
        $this->Index();
    }

    public function Edit(){
        if( count($_POST) >0 && !multi_submit() ){
            global $DB;
            $DB->query("update ".TB_PREFIX."news set 
			title= '{$_POST["title"]}', text= '".$DB->escape_string($_POST["text"])."', link= '{$_POST["link"]}', graphic= '{$_POST["graphic"]}', datetime= NOW() where id= ".(int)$_POST["id"]);
			
			#echo ("update ".TB_PREFIX."news set 
			#title= '{$_POST["title"]}', text= '{$_POST["text"]}', link= '{$_POST["link"]}', graphic= '{$_POST["graphic"]}', datetime= NOW() where id= ".(int)$_POST["id"]);
			
        }
        $this->Index();
    }
}