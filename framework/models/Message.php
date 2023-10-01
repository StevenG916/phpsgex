<?php
namespace phpsgex\framework\models;

use phpsgex\framework\MessageType;

class Message extends ModelLoader{
    public $id, $from, $to, $mtit, $time, $read, $mtype, $aiid;
    protected $text;
    
    public function __construct($id, Array $row= null){
        if( $row == null ){
            global $DB;
            $row= $DB->query("select * from ".TB_PREFIX."user_message where id= $id")->fetch_array();
        }
        
        parent::__construct($row);
    }
    
    public function GetText(){
        $t= $this->text;
        
        if( $this->mtype == MessageType::AllyInvite ){
            $t.= "<br><a href='?pg=Ally&act=Invite&a=1&message={$this->id}' class='button'>Accetta</a> "
                    ."<a href='?pg=Ally&act=Invite&a=0&message={$this->id}' class='button'>Rifiuta</a>";
        }
        
        return $t;
    }
    
    public function Delete(){
        global $DB;
        $DB->query("delete from ".TB_PREFIX."user_message where id= ".$this->id);
    }
}