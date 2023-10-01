<?php
namespace phpsgex\controllers;

use phpsgex\framework\Auth;
use phpsgex\framework\models\User;
use phpsgex\framework\models\ally\Ally;
use phpsgex\framework\models\ally\AllyCharge;

class AllyController extends BaseController {
	public $ally= null;

	public function Create(){
		if( count($_POST) ==0 || multi_submit() || $this->user->allyId != null )
		return $this->Index();

		global $DB;
		$DB->query("insert into ".TB_PREFIX."ally values
		(null, '{$_POST["name"]}', '".htmlspecialchars($_POST["desc"])."', '', ".$this->user->id.", 0)");
		

		if( $DB->error != null ){
		$this->viewData["error"]= "Errore nella creazione dell alleanza! controlla che il nome non sia gia stato utilizzato";
			error_log($DB->error);
			return parent::Index("ally/create");
		}
		#prende il valore dell'id dell'alleanza appena creata
        $idally = $DB->insert_id;
		
		#dopo aver creato l'alleanza modifica il campo id_ally
		$DB->query("update ".TB_PREFIX."users
					set ally_id= {$DB->insert_id}
					where id= ".$this->user->id);
		
		#crea la carica del fondatore
		$DB->query("insert into ".TB_PREFIX."ally_charge values 
		    (null, $idally, 'Fondatore', 'colui che ha fondato questa alleanza', 'nessuna.png', 1, 0, 0, 0, 0)");
				
		#prende il valore dell'id della carica del fondatore
        $idcharge = $DB->insert_id;	
			
		#imposta al fondatore dell'alleanza la carica appena creata			
		$DB->query("insert into ".TB_PREFIX."ally_user_role values
					(null, $idally, ".$this->user->id.", $idcharge)");
					
		#crea una carica (nessun ruolo) in automatico dopo aver creato l'alleanza			
		$DB->query("insert into ".TB_PREFIX."ally_charge values 
		    (null, $idally, 'Nessun Ruolo', 'Player Appartenente alla alleanza senza nessun incarico', 'nessuna.png', 0, 0, 0, 0, 0)");
					
		
		header("Location: ?pg=Ally");
	}

	public function Edit(){
		if( $this->user->allyId == null )
			return $this->Index();

		$this->ally= new Ally( $this->user->allyId );
		if( count($_POST) ==0 ) {
			return parent::Index("ally/edit");
		} else if( $this->ally->ownerId == $this->user->id ){
			#echo "update ".TB_PREFIX."ally
			#	  set desc= '".htmlspecialchars($_POST["desc"])."'
			#	  where id= ".$this->user->allyId;
			global $DB;
			$DB->query("update ".TB_PREFIX."ally "
					. "set `desc`= '{$_POST["desc"]}' "
					. "where id= ".$this->user->allyId);

			header("Location: ?pg=Ally");
		}
		$this->Index();
	}

	public function Search(){
		global $DB;
		$qr= $DB->query("select * from ".TB_PREFIX."ally 
		    where name= '{$_GET["name"]}'");
		if( $qr->num_rows != 0 )
			$this->ally= new Ally(0, $qr->fetch_array());
		else $this->viewData["error"]= "Nessuna Alleanza trovata con questo nome: ".$_GET["name"];

		parent::Index();
	}
	public function Leave(){
		global $DB;
		
		#elimina il ruolo il ally_user_role
		$qr= $DB->query("DELETE FROM `".TB_PREFIX."ally_user_role`
		    WHERE `allyId`= {$this->user->allyId} and userId = {$this->user->id}");
		# successivamente imposta il valore null nel campo id della tabella users
		$qr= $DB->query("UPDATE `".TB_PREFIX."users` 
		    SET `ally_id` = null 
		    WHERE `id` = ".$this->user->id."");
			
		header("Location: ?pg=Ally");
	}
	######################################################################################
	#funzione creazione cariche, Aik                                                     #
	######################################################################################

    public function Admin(){
        if( $this->user->allyId == null )
        return $this->Index();

		$this->ally= new AllyCharge( $this->user->allyId );
		if( count($_POST) == 0 ) {
			return parent::Index("ally/admin");
		} else {
		
		global $DB;
		$dbImg=null;
        $directory='uploads/ally_charge/ally_'.$this->user->allyId.'/';

        $_SESSION['message_insert_charge']=true;
        if(!empty($_FILES["fileToUpload"]['name'])) {
            $dbImg=$directory.$_FILES["fileToUpload"]['name'];
            if (ImageUpload::Upload($directory, $_FILES["fileToUpload"]) == 0) {
                $_SESSION['message_insert_charge'] = false;
                $DB->query("insert into ".TB_PREFIX."ally_charge values
					(null, ".$this->user->allyId.", '{$_POST["name"]}', '".htmlspecialchars($_POST["desc"])."', '$dbImg', '{$_POST["admin_full"]}', '{$_POST["admin_recruit"]}', '{$_POST["admin_wars"]}', '{$_POST["admin_reliable"]}', '{$_POST["admin_forum"]}')");
									
                if( $DB->error != null ){
                    $this->viewData["error"]= "Errore nella crezione della carica!";
                    error_log($DB->error);
                    return parent::Index("ally/admin");
                }
            }
        }
        else{
            $_SESSION['uploaded']=true;
            $_SESSION['message_insert_charge'] = "false";
        }
		return parent::Index("ally/admin");			
	}	
	}

		public function DeleteCharge(){
        if( isset($_GET["id"]) && $_GET["id"] != 1 ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."ally_charge
                        where id= ".(int)$_GET["id"]);
			#rimove la carica assegnata al player impostando il valore 0 che corrisponde a nessuna carica
		     $DB->query("update ".TB_PREFIX."ally_user_role set 
						`chargeId`= null where `chargeId`= ".(int)$_POST["id"]);
        }
        return parent::Index("ally/admin");
    }
	
	 public function ModCharge(){
        if( count($_POST) >0 && !multi_submit() ){
		#se carichi una nuova immagine per la carica	

            global $DB;
            $DB->query("update ".TB_PREFIX."ally_charge set 
			`name`= '{$_POST["name"]}', `desc`= '".$DB->escape_string($_POST["desc"])."', `img`= '{$_POST["img"]}', `adm_full`= '{$_POST["admin_full"]}', `adm_recruit`= '{$_POST["admin_recruit"]}', `adm_wars`= '{$_POST["admin_wars"]}', `adm_reliable`= '{$_POST["admin_reliable"]}', `adm_forum`= '{$_POST["admin_forum"]}' where id= ".(int)$_POST["id"]);
        }
        return parent::Index("ally/admin");
    }

	
    public function PannelAdmin(){
    if( $this->user->allyId == null )
        return $this->Index();

        else {
            return parent::Index("ally/acp/menu");
        }
    }
	
#####################################################################################################
#Gestione diplomazia Aik                                                                           ##
#####################################################################################################
    public function warlist(){
		$list = $_POST["ally_name"];
		$arr= array();
		#echo $list;	
		
		global $DB;
		
	   $query =  $DB->query("select name,id from ".TB_PREFIX."ally where name like \"%{$list}%\" ");
		while ($row = $query->fetch_array()){ $arr[] = $row; }
		echo json_encode($arr);
		#exit();
		
			
    }
    public function Wars(){
        if( $this->user->allyId == null )
            return $this->Index();

		#$this->ally= new Ally( $this->user->allyId );
		if( count($_POST) ==0 ) {
			return parent::Index("ally/wars");
		}
		#else {
		#if( $_POST["chargeId"] ==0 ) {
		#global $DB;
		#$DB->query("update ".TB_PREFIX."ally_user_role set 
		#`chargeId`= null where userId= ".(int)$_POST["userId"]);
		
		#return parent::Index("ally/wars");
		#} 
        /*else {
            global $DB;

            #$ally2 = Ally::GetByName($_POST["ally2"])->id;
            #$ally2= Ally::Instantiate($_POST["ally2"])->id;
            #echo $ally2;
            $DB->query("insert into ".TB_PREFIX."ally_pact values
            (".$this->user->allyId.", '$ally2', '{$_POST["type"]}','{$_POST["status"]}' ,NOW(), '0000-00-00 00:00:00')");
                    echo  ("insert into ".TB_PREFIX."ally_pact values
            (".$this->user->allyId.", '$ally2', '{$_POST["type"]}','{$_POST["status"]}' ,NOW(), '0000-00-00 00:00:00')");

            if( $DB->error !== null ){
                #$DB->query("update ".TB_PREFIX."ally_user_role set
                #`chargeId`= '{$_POST["chargeId"]}' where userId= ".(int)$_POST["userId"]);
                $this->viewData["error"]= "errore nella gestione delle diplomazia ";
                //return parent::Index("ally/wars");
            }
            return parent::Index("ally/wars");
        } */
    }
  	
#####################################################################################################
# fine funzione script creazione cariche                                                            #
#####################################################################################################

#####################################################################################################
#inizio script assegnazione ruoli, Aik                                                              #
#####################################################################################################
		public function AdminRole(){
			if( $this->user->allyId == null )
			return $this->Index();

		$this->ally= new Ally( $this->user->allyId );
		if( count($_POST) ==0 ) {
			return parent::Index("ally/role");
			
		} else {
		if( $_POST["chargeId"] ==0 ) {
		global $DB;
		$DB->query("update ".TB_PREFIX."ally_user_role set 
		`chargeId`= null where userId= ".(int)$_POST["userId"]);
		
		return parent::Index("ally/role");
		} else {	
		global $DB;
		$DB->query("insert into ".TB_PREFIX."ally_user_role values
					(null, ".$this->user->allyId.", '{$_POST["userId"]}', '{$_POST["chargeId"]}')");
                    
		if( $DB->error !== null ){
			$DB->query("update ".TB_PREFIX."ally_user_role set 
			`chargeId`= '{$_POST["chargeId"]}' where userId= ".(int)$_POST["userId"]);
			return parent::Index("ally/role");
        }			
			return parent::Index("ally/role");
	}
	}
	}

		public function DeleteRole(){
        if( isset($_GET["idCharge"]) && $_GET["idCharge"] != 1 ){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."ally_user_role
                        where userId= ".(int)$_GET["idCharge"]." and allyId=".$this->user->allyId);
			
            if( $DB->error !== null ){
                $DB->query("update ".TB_PREFIX."users set 
						`ally_id`= null where id= ".(int)$_GET["idCharge"]);
            }
        }
        return parent::Index("ally/role");
    }
#####################################################################################################
#fine script assegnazione ruoli                                                                     #
#####################################################################################################

#####################################################################################################
#script reclutamento / Inviti aik                                                                   #
#####################################################################################################
public function Recruit(){
			
		if( $this->user->allyId == null ) return $this->Index();
		
		if( count($_POST) ==0 ) {
			return parent::Index("ally/recruit");
		} else { 
		try {		 
        $to_userId = User::GetByName($_POST["name"])->id;
        } catch(\Exception $ex){
			$this->viewData["error"]= "Non esiste il player con seguente nome: ".$_POST["name"];
			return parent::Index("ally/recruit");
        }
		global $DB;
	    # controlla se il player ha gia un invito accettato per questa alleanza
		$qr= $DB->query("select * from ".TB_PREFIX."ally_invites 
		    where to_userId= $to_userId and status = 0 and allyId= ".$this->user->allyId );
		if( $qr->num_rows ==0 ){
		
		$DB->query("insert into ".TB_PREFIX."ally_invites values
		    (null, ".$this->user->allyId.", '$to_userId', ".$this->user->id.",".$_POST["chargeId"].", 0, NOW())");

		#manda messaggio invito 
		$this->ally= new Ally( $this->user->allyId );
        SendMessage( $this->user, $to_userId, "Invit :{$this->ally->name}",
            $this->ally->inviteText, \phpsgex\framework\MessageType::AllyInvite, $this->ally);

		if( $DB->error != null ){
			$this->viewData["error"]= "Hai gia inoltrato l invito al player selezionato!";
			error_log($DB->error);
			return parent::Index("ally/recruit");
        }
		return parent::Index("ally/recruit");			
}
       $this->viewData["error"]= "Hai gia inoltrato l invito al player selezionato!";	
        return parent::Index("ally/recruit");	
	}	
	}
	
	        public function Invite(){
            if( count($_GET) ==0 ) 
                return $this->Index();
            
        if( isset($_GET["a"])){
	#controlla se il player è in una alleanza
	if( $this->user->allyId != null ){
	# se il valore è diversa da null abbandona l'alleanza prima di eseguire le query per entrare a far parte
	global $DB;
	#elimina il ruolo il ally_user_role
	$qr= $DB->query("DELETE FROM `".TB_PREFIX."ally_user_role` 
	    WHERE `allyId`= ".$this->user->allyId." and userId = ".$this->user->id."");
	
	# successivamente imposta il valore null nel campo id della tabella users
	$qr= $DB->query("UPDATE `".TB_PREFIX."users` 
	    SET `ally_id` = null 
	    WHERE `id` = ".$this->user->id."");
	
	}				
                #if( (bool)$_GET["a"] ){
					global $DB;
                    $msg= new \phpsgex\framework\models\Message( (int)$_GET["message"] );
			 		$this->ally= new AllyInvites( $this->user->id );
					
             	 $DB->query("update ".TB_PREFIX."users "
               . "set ally_id= {$this->ally->allyId} "
               . "where id= ".$this->user->id);
			
					#assegna la carica stabilita dall'invito
					$DB->query("insert into ".TB_PREFIX."ally_user_role values
					    (null, {$this->ally->allyId}, ".$this->user->id.", {$this->ally->charge_id})");
					
					#dopo aver accettato l'invito questo cambia il suo status da 0 a 1(non piu visualizzabile)
					$DB->query("update ".TB_PREFIX."ally_invites set 
					    `status`= 1 where allyId= {$this->ally->allyId}  and charge_id= {$this->ally->charge_id} ");
					
                    #dopo aver accettato elimina il messaggio
                   $msg->Delete();
                }
          #  } 
            header("Location: ?pg=Ally");
        }

		public function DeleteInvite(){
        if( isset($_GET["id1"])){
            global $DB;
            $DB->query("delete from ".TB_PREFIX."ally_invites
            where id= ".(int)$_GET["id1"])." and 'allyId' = ".$this->user->allyId." and 'status' = 0 ";
			#elimina invito inoltrato nel messaggio
            $DB->query("delete from ".TB_PREFIX."user_message
            where `to`= ".$_GET["idname"])." and `from` = ".$_GET["idfromname"]." and `aiid`= ".$this->user->allyId."  and `mtype` = 3 )";
			
        }
        return parent::Index("ally/recruit");
   		}
		
#####################################################################################################
# fine script reclutamento / inviti aik                                                             #
#####################################################################################################

	public function Index(){
		if( isset($_GET["id"]) ){
			try{
			$this->ally= new Ally( (int)$_GET["id"] );
			} catch(\Exception $ex){
				$this->viewData["error"]= "Invalid ally id: ".$_GET["id"];
			}
		} else if( $this->user->allyId != null )
		    $this->ally= new Ally( $this->user->allyId );

		if( $this->ally != null )
			parent::Index("ally/index");
		else
			parent::Index("ally/home");
	}
	
public function Cancel(){
        
            global $DB;
			#cancella tutti gli inviti
            $DB->query("delete from ".TB_PREFIX."ally_invites
            where allyId= ".$this->user->allyId) ;
			#cancella i ruoli
			$DB->query("delete from ".TB_PREFIX."ally_user_role
            where allyId= ".$this->user->allyId) ;
			#cancella tutte le cariche
			$DB->query("delete from ".TB_PREFIX."ally_charge
            where id_ally= ".$this->user->allyId) ;
			#cancella l'alleanza
			$DB->query("delete from ".TB_PREFIX."ally
            where id= ".$this->user->allyId) ;
			#rimuovi tutti i player dell'alleanza
			$DB->query("update ".TB_PREFIX."users "
               . "set ally_id= null "
               . "where ally_id= ".$this->user->allyId) ;

        
        header("Location: ?pg=Ally");
   		}
		}
