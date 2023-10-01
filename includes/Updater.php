<?php
namespace phpsgex\includes;

use phpsgex\framework\models\Config;

final class Updater{
    public static function UpdateDb(){
        global $DB; $conf= Config::Instance();

		while( $conf->sge_ver <= DBVER ){
			//update scripts
			if( $conf->sge_ver <= 131 ){
				$qr= $DB->query("select u.email from ".TB_PREFIX."users as u 
								where u.id in (select id from ".TB_PREFIX."users 
												where username= u.username and id <> u.id)");
				while($row= $qr->fetch_array()){
					mail($row["email"], $conf->server_name." username change", "Your username was changed with your email because to engine changes. \nYou can choose a new username in your profile settings");
				}
				$DB->query("update ".TB_PREFIX."users set username= email 
							where id in (select u.id from ".TB_PREFIX."users as u 
										where u.id in (select id from ".TB_PREFIX."users 
														where username= u.username and id <> u.id))");
			}
			
			$update= __ROOT__."includes/dbscripts/".Config::Instance()->sge_ver.".sql";
			if( !file_exists($update) ){
				error_log("DB update '$update' doesn't exist!");
				return;
			}
			$qr= file_get_contents($update);
			$qr= preg_replace( "'%PREFIX%'", TB_PREFIX, $qr );

			if($DB->multi_query($qr)){
				do {
					if($result= $DB->store_result())
						$result->free();
				} while( $DB->more_results() && $DB->next_result() );
			} else die("Update error: ".$DB->error."<br>Db Version: ".$conf->sge_ver);
			
			Config::$instance= null; //need to reload version
		}
    }

    public static function UpdateConfig(){

    }
}