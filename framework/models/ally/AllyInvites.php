<?php
namespace phpsgex\framework\models\ally;
use phpsgex\framework\Auth;

class AllyInvites {
	public $id, $allyId, $to_userId, $from_userId, $charge_id, $status, $data;
	
    public function __construct( $_id, Array $row= null ){
        global $DB;
        if( $row == null ){
            $qr= $DB->query("select * from ".TB_PREFIX."ally_invites
            where  to_userId= $_id and status = 0" );

            $row= $qr->fetch_array();

        }

        $this->id = $row["id"];
        $this->allyId = $row["allyId"];
        $this->to_userId = $row["to_userId"];
        $this->from_userId = $row["from_userId"];
        $this->charge_id = $row["charge_id"];
        $this->status = $row["status"];
        $this->data = $row["data"];
    }

    public static function GetAll(){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."ally_invites
            where allyId = ".Auth::Instance()->GetUser()->allyId." and status = 0 order by id asc");
        $ret= Array();
        while($row= $qr->fetch_array())
        $ret[]= new AllyInvites(0, $row);
        return $ret;
    }

    public static function Okinvite(){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."ally_invites
            where to_userId = ".Auth::Instance()->GetUser()->id." order by id asc");
        $row= $qr->fetch_array();
        return $row['result'];
    }
};



?>