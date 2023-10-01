<?php
namespace phpsgex\framework\models;

use phpsgex\framework\models\research\ResearchData;

class User {
	public $id, $raceId, $name, $capitalCityId, $email, $timestampReg, $rank, $allyId, $lastLogin, $tutorial, $language, $ip,
        $points, $points_atk, $points_def, $points_sup;

    public static $_instance;
    public static function Instantiate( $id, Array $_row= null ){
        return call_user_func( self::$_instance, $id, $_row );
    }

	public function __construct( $_id, Array $info= null ){
        if($_id <= 0 && $info == null)
            throw new \Exception("invalid id $_id");

        if($info==null) {
            global $DB;
            $query = $DB->query("select * from " . TB_PREFIX . "users where id= $_id");
            $info = $query->fetch_array();
        }

        $this->id = $info["id"];
        $this->name = $info["username"];
        $this->raceId = $info["race"];
        $this->capitalCityId = $info["capcity"];
        $this->allyId = $info["ally_id"];
        $this->email = $info["email"];
        $this->timestampReg = $info["timestamp_reg"];
        $this->points = $info["points"];
        $this->rank = $info["rank"];
        $this->lastLogin = $info["last_log"];
        $this->tutorial = $info["tut"];
        $this->language = $info["lang"];
        $this->ip = $info["ip"];
        $this->points_atk = $info["points_attack"];
        $this->points_def = $info["points_defence"];
        $this->points_sup = $info["points_supporter"];
	}

    public static function GetByName($name){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."users
                         where username= '$name'");
        if( $qr->num_rows ==0 ) throw new \Exception("Username not found");
        return self::Instantiate(0,$qr->fetch_array());
    }

	//return a list of City
	public function GetCities(){
		global $DB;
		$cities= Array();
		
		$query= $DB->query("select * from ".TB_PREFIX."city where owner = ".$this->id);
		while( $row= $query->fetch_array() ){
			$cities[]= City::Instantiate(0, $row, $this );
		}
		
		return $cities;
	}

    /**
     * @param int $_cityId
     * @return City
     * @throws Exception
     */
    public function GetCity( $_cityId ){
        global $DB;
        $query= $DB->query("select * from ".TB_PREFIX."city where owner= {$this->id} and id= $_cityId");
        if( $query->num_rows != 1 ) throw new \Exception();

        return City::Instantiate(null, $query->fetch_array(), $this );
    }

    /**
     * @return bool
     */
    public function IsOnline(){
        $ll= $this->lastLogin;

        $ymg = explode(" ", $ll);
        $s = explode("-", $ymg[0]);
        $Y = $s[0];
        $M = $s[1];
        $G = $s[2];

        $z = explode(":", $ymg[1]);
        $h = $z[0];
        $m = $z[1];
        $s = $z[2];

        $tmll= ( time() - mktime($h,$m,$s,$M,$G,$Y) ) / 60;
        return $tmll < 5;
    }

    /**
     * @param int $_researchId
     * @param bool $_queue
     * @return int
     */
    public function GetResearchLevel( $_researchId, $_queue = false ){
        global $DB;
        $qrbuildedinfo= $DB->query("SELECT lev FROM ".TB_PREFIX."user_research WHERE id_res = $_researchId AND usr = {$this->id}");
        if( $qrbuildedinfo->num_rows >0 ) {
            $abli= $qrbuildedinfo->fetch_array();
            $lev= $abli['lev'];
        } else $lev= 0;

        if( !$_queue ) return $lev;
        else {
            $qr= $DB->query("SELECT * FROM `".TB_PREFIX."city_research_que` WHERE `city` in (select id from ".TB_PREFIX."city where owner= {$this->id}) AND `res_id`= $_researchId");
            return $lev + $qr->num_rows;
        }
    }

    public function GetResearchQueueTime( $_researchId= null ){
        global $DB;
        $qr= $DB->query("select max(`end`) from ".TB_PREFIX."city_research_que
                        where id in (select id from ".TB_PREFIX."city where owner= {$this->id})"
                        .($_researchId== null ? "" : "and res_id= $_researchId") );

        $t= $qr->fetch_array()[0];
        return $t ==0 ? $t : $t -time();
    }

    public function GetResearchTime( ResearchData $_research, $_nextLevel ){
        return $_research->GetResearchTime( $_nextLevel ) +$this->GetResearchQueueTime( $_research->id );
    }

    public function GetResearchQueue(){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."city_research_que
                        where city in (select id from ".TB_PREFIX."city where owner= {$this->id})");

        $ret= array();
        while($row= $qr->fetch_array())
            $ret[]= new ResearchQueue($row);
        return $ret;
    }
    
    public function FetchResearchQueue(){
        global $DB;
        //search if there in a build in the que - return the resting time
        $bqs= $DB->query("SELECT * FROM ".TB_PREFIX."city_research_que "
                        . "WHERE `end` <= ".time()." and `city` in (select id from ".TB_PREFIX."city where owner= {$this->id})");
        while( $rab= $bqs->fetch_array() ){
            //build
            $qcb= $DB->query("SELECT * FROM ".TB_PREFIX."t_research WHERE `id`= ".$rab['res_id']);
            $acb= $qcb->fetch_array();
            //level control!
            $lev= $this->GetResearchLevel($rab['res_id']);
            if( $lev ==0 ){ // verifica sul livello 0 - se non c'è costruzisce livello 1
                //$qadf=""; $qaf="";
                //if(CITY_SYS==2){$qadf=" ,`field`"; $qaf=", '".$rab['field']."'";}
                $DB->query("INSERT INTO `".TB_PREFIX."user_research` (`id_res`, `usr`, `lev`) VALUES 
                    ({$rab['res_id']}, {$this->id}, 1);");
            } else { //altrimenti aumenta il livello
                $lcb= $lev+1;
                $DB->query("UPDATE `".TB_PREFIX."user_research` SET `lev`= $lcb WHERE `id_res`= {$rab['res_id']} AND `usr`= ".$this->id);
            }
            $DB->query("DELETE FROM ".TB_PREFIX."city_research_que WHERE id=".$rab['id']);
            //add points
            $this->AddResearchPoints($acb['gpoints']);
        }
    }

    public function IsBanned(){
        global $DB;
        $qrban= $DB->query("SELECT timeout FROM ".TB_PREFIX."banlist WHERE usrid = ".$this->id);
        if( $qrban->num_rows ==0 ) return false;
        $abq= $qrban->fetch_array();
        if( $abq['timeout'] == -1 ) return true; //forever banned!
        if( $abq['timeout'] > time() ) return true;
        $DB->query("DELETE FROM ".TB_PREFIX."banlist WHERE usrid = ".$this->id);
        return false;
    }

    /**
     * used for research points
     * @param $value
     */
    public function AddResearchPoints($value){
        global $DB;
        $this->points += $value;
        $DB->query("UPDATE `".TB_PREFIX."users` 
            SET `points` = {$this->points},`last_log` = NOW( ) 
            WHERE `id`= ".$this->id);
    }

    public function AddAtkPoints($value){
        global $DB;
        $this->points_atk += $value;
        $DB->query("UPDATE `".TB_PREFIX."users` 
            SET `points_attack` = {$this->points_atk},`last_log` = NOW( ) 
            WHERE `id`= ".$this->id);
    }

    public function GetTotalPoints(){
        global $DB;
        $villagePoints= $DB->query("select sum(points) from ".TB_PREFIX."city 
                            where owner= ".$this->id)->fetch_array()[0];

        return $this->points + $villagePoints + $this->points_atk + $this->points_def + $this->points_sup;
    }
};

User::$_instance= function($id, Array $row= null){ return new User($id,$row); };