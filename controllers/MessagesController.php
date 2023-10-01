<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\Message;
use phpsgex\framework\models\User;
use phpsgex\framework\models\City;
use \phpsgex\framework\models\units\UnitData;
use \phpsgex\framework\models\units\Unit;
use \phpsgex\framework\battle\BattleResult;

class MessagesController extends BaseController {

    protected function GetMessages($type){
        if(!isset($_GET['site']) || (isset($_GET['site']) && (!is_numeric($_GET['site'])))){
            $site = 1;
        }else{
            $site = ($_GET['site']);
        }
        // Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella
        global $DB;
        $num_rows = $DB->query("select * from ".TB_PREFIX."user_message where `to`= ".$this->user->id." and mtype='{$type}' order by `time` desc")->num_rows;

        // variabile contenente il numero di messaggi visualizzati per pagina
        $mails_per_site = 10;
        // Tramite una semplice operazione matematica definisco il numero totale di pagine
        $num_sites=(($num_rows%$mails_per_site)==0) ? $num_rows/$mails_per_site : ceil($num_rows/$mails_per_site);
        $this->viewData["num_sites"]= $num_sites;
        //pagina di partenza
        $start = ($site-1)*$mails_per_site;

        // creazione del ciclo per l'impaginazione dinamica
        #global $links;
        $links = "";
        for ($i = 1; $i <= $num_sites; $i++) {
            $links .= ($i != $site ) ? "<a href='?pg=Messages&site=$i'> $i</a> " : ">$site<" ;
            $this->viewData["links"]= $links;
        }

        $ret= array();
        global $DB;
        $qr= $DB->query("SELECT * FROM ".TB_PREFIX."user_message
                         WHERE `to`= ".$this->user->id." and mtype='{$type}' ORDER BY `time` DESC LIMIT $start, $mails_per_site");
        while($row= $qr->fetch_array())
            $ret[]= new Message(0, $row);
        return $ret;
    }
	
    public static function GetUnreadMessagesNumber(){
        global $DB;
        return $DB->query("select id from ".TB_PREFIX."user_message 
            where `read`= false and `mtype`=0 and `to`= ".Auth::Instance()->GetUser()->id)->num_rows;
    }

    public static function GetUnreadReportNumber(){
        global $DB;
        return $DB->query("select id from ".TB_PREFIX."user_message 
            where `read`= false and `mtype`=2 and `to`= ".Auth::Instance()->GetUser()->id)->num_rows;
    }

    public function Delete(){
		global $DB;
        
        if(!is_null($_POST["id"]) ){
            foreach( $_POST["id"] as $key => $value )
                $DB->query("delete from ".TB_PREFIX."user_message where id=$key");
		}else {
            $DB->query("delete from ".TB_PREFIX."user_message
                        where id= ".(int)$_GET["id"]." and `to`= ".$this->user->id);
		}

        return $this->Index();
    }
	
	public function cancella_tutto(){
        global $DB;
        foreach( $_POST["id"] as $key => $value )
            $DB->query("delete from ".TB_PREFIX."user_message where id= ".(int)$key);
        return $this->Index();
    }

    public function Send(){ 
        if( multi_submit() || !isset($_POST["name"]) || !isset($_POST["title"]) || !isset($_POST["text"]) )
            return $this->Index();

        $from= $this->user->id;

        try {
            $to = User::GetByName($_POST["name"])->id;
        } catch(\Exception $ex){
            $this->viewData["error"]= "Invalid sender username: ".$_POST["name"];
            return $this->Index();
        }
        
        global $DB;
        $DB->query("insert into ".TB_PREFIX."user_message
                    values(null, $from, $to, '{$_POST["title"]}', NOW(), '{$_POST['text']}', false, 0, null)");

        header("Location: ?pg=Messages");
        $this->Index();
    }

    public function readID(){ 
        global $DB;
        $id = (int) filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $qr = $DB->query("select * from ".TB_PREFIX."user_message
                         where `id`= {$id} and `to`= ".$this->user->id);
        if($qr->num_rows != 0){
           $row= $qr->fetch_array();
           $br = json_decode($row['text']); 
           //if luck is -
           if($br->luck < 0){
                $pr = '<div class="progressStatBar neg"><div class="childrenStatBar" style="width: '.str_replace("-", "", $br->luck).'%;"></div></div>
                      <div class="progressStatBar plus"><div class="childrenStatBar" style="width: 0%;"></div></div>';
           }else{
                $pr = '<div class="progressStatBar neg"><div class="childrenStatBar" style="width: 0%;"></div></div>
                       <div class="progressStatBar plus"><div class="childrenStatBar" style="width: '.str_replace("-", "", $br->luck).'%;"></div></div>';
           }

            $unt_q = [];
            $unt_lost = [];
            $unt_q_df = [];
            $unt_lost_df = [];

            foreach( $br->atkUnits as $i => $unit ){ // Unit $unit Attack
                $unt_q[$unit->unit->name] = [0 => $i, 2 => $unit->quantity];
                $unt_lost[$unit->unit->name] = [0 => $i, 2 => ( array_key_exists($i, $br->atkUnitsLost) ? $br->atkUnitsLost[$i] : 0)];
            }

            foreach( $br->defUnits as $i => $unit ){ // Unit $unit Def
                $unt_q_df[$unit->unit->name] = [0 => $i, 2 => $unit->quantity];
                $unt_lost_df[$unit->unit->name] = [0 => $i, 2 => ( array_key_exists($i, $br->defUnitsLost) ? $br->defUnitsLost[$i] : 0)];
            }

            if($br->status == "attack"){
                echo '<h2>Hai '.($br->winner == BattleResult::Win_Atk ? "Vinto" : "Perso").'!</h2>';
            }else if($br->status == "defense"){
                echo '<h2>Hai '.($br->winner == BattleResult::Win_Def ? "Vinto" : "Perso").'!</h2>';
            }else{
                echo '<h2>Hai Vinto!</h2>';
            }
            echo '   
                <br>
                <div="">
                    <b><i>Fortuna dell\'attaccante</i></b>
                    <div class="fsd_">
                        <b>'.$br->luck.'%</b>
                        <div class="progressBar central">
                            '.$pr.'
                        </div>
                    </div>
                    <b>Morale: '.$br->moral.'%</b>
                </div> 
                <br>
                <table style="padding:0px;" class="rep">
                    <tr>
                        <th style="width:20px">Attaccante</th>
                        <th>'.User::Instantiate($br->atkUnits[0]->ownerId)->name.'</th>
                    </tr>
                    <tr>
                        <td style="width:20px">Provenienza:</td>
                        <td>'.City::Instantiate($br->atkUnits[0]->from)->name.' ('.City::Instantiate($br->atkUnits[0]->from)->mapPosition->x.'|'.City::Instantiate($br->atkUnits[0]->from)->mapPosition->y.')</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 0px;">
                            <table width="">
                                <tr>
                                    <td style="width:20%">&nbsp;</td>
                                    ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                        if(@$unt_q[$unt->name][2] != null){
                                            $n = $unt_q[$unt->name][2];
                                        }else{
                                            $n = 0;
                                        }
                                        echo '
                                            <td ><a class="unit_link" quant="'.$n.'" href="#" data-unit="spear"><img src="templates/images/units/'.$unt->image.'"  width="18px" title="'.$unt->name.'" alt="" class=""></a></td>
                                        ';
                                    }
                        echo '
                                </tr>
                                <tr>
                                    <td style="width:20%">Quantità:</td>
                            ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                       
                                        echo "<td>&nbsp;".($unt_q[$unt->name][2] ? $unt_q[$unt->name][2] : 0)."</td>";
                                    }
                        echo '
                                </tr>
                                <tr>
                                    <td style="width:20%">Perdite:</td>
                            ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                            
                                        echo "<td>&nbsp;".($unt_lost[$unt->name][2] ? $unt_lost[$unt->name][2] : 0)."</td>";
                                    }
                        echo '
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            <br>
                <table style="padding:0px;" class="rep">
                    <tr>
                        <th style="width:20px">Diffensore</th>
                        <th>'.$br->city->user->name.'</th>
                    </tr>
                    <tr>
                        <td style="width:20px">Provenienza:</td>
                        <td>'.$br->city->name.' ('.$br->city->mapPosition->x.'|'.$br->city->mapPosition->y.')</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 0px;">
                            <table width="">
                                <tr>
                                    <td style="width:20%">&nbsp;</td>
                                ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                        if(@$unt_q_df[$unt->name][2] != null){
                                            $n = $unt_q_df[$unt->name][2];
                                        }else{
                                            $n = 0;
                                        }
                                        echo '
                                            <td ><a class="unit_link" quant="'.$n.'" href="#" data-unit="spear"><img src="templates/images/units/'.$unt->image.'"  width="18px" title="'.$unt->name.'" alt="" class=""></a></td>
                                        ';
                                    }
                            echo '
                                </tr>
                                <tr>
                                    <td style="width:20%">Quantità:</td>
                            ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                        
                                        echo "<td>&nbsp;".($unt_q_df[$unt->name][2] ? $unt_q_df[$unt->name][2] : 0)."</td>";
                                    }
                        echo '
                                </tr>
                                <tr>
                                    <td style="width:20%">Perdite:</td>
                            ';
                                    foreach(UnitData::GetAll() as $unt){ 
                                            
                                        echo "<td>&nbsp;".($unt_lost_df[$unt->name][2] ? $unt_lost_df[$unt->name][2] : 0)."</td>";
                                    }
                        echo '
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br><br>';
        }
    } 

    public function Report(){
        global $DB;
        $DB->query("update ".TB_PREFIX."user_message
                    set `read`= true
                    where `mtype`=2 and `to`= ".$this->user->id);
        $this->viewData["report"]= $this->GetMessages(2);
        parent::Index("report");
    }

    public function Index(){
        global $DB;
        $DB->query("update ".TB_PREFIX."user_message
                    set `read`= true
                    where `mtype`=0 and `to`= ".$this->user->id);
        $this->viewData["messages"]= $this->GetMessages(0);
        parent::Index("messages");
    }
}