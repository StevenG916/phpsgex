<?php
namespace phpsgex\framework\models;

class LoggedUser extends User {
    private $currentCity;

    public function __construct( $_id ){
        parent::__construct($_id);
        global $DB;
        $DB->query("update ".TB_PREFIX."users set `last_log` = NOW() where id= ".$this->id);
        $this->currentCity= City::Instantiate($this->capitalCityId);
    }

    public function SwitchCity( $cityId ){
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."city where id= $cityId and owner= ".$this->id);
        if( $qr->num_rows ==0 ) throw new \Exception("Invalid cityId: $cityId");

        $this->currentCity= City::Instantiate(null, $qr->fetch_array(), $this );
        $DB->query("update ".TB_PREFIX."users set capcity= $cityId where id= ".$this->id);
    }

    /**
     * @return City
     */
    public function GetCurrentCity(){
        return $this->currentCity;
    }

    public function SendMessage( $_to, $_tittle, $_body, $_messageType= 1, Ally $allyInvite= null ){
        SendMessage($this, $_to, $_tittle, $_body, $_messageType, $allyInvite);
    }
};