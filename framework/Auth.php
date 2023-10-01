<?php
namespace phpsgex\framework;

use phpsgex\framework\interfaces\ILoginBridge;
use phpsgex\framework\models\Config;
use phpsgex\framework\models\User;
use phpsgex\framework\models\LoggedUser;
use phpsgex\framework\models\City;
use phpsgex\framework\exceptions\LoginException;
use phpsgex\framework\exceptions\RegistrationException;

class Auth implements ILoginBridge {
    private static function CryptPassword( $_password ){
        return md5($_password);
    }

    public static $_instance;

    private static $bridges= [];
    
    public static function AddBridge(ILoginBridge $br){
        self::$bridges[]= $br;
    }
    
    /**
     * @return ILoginBridge
     */
    public static function Instance(){
        return self::$_instance;
    }

    private static $user= null;

    /**
     * @return null|LoggedUser
     */
    public function GetUser(){
        if( isset($_SESSION["userId"]) && self::$user == null )
            self::$user= new LoggedUser($_SESSION["userId"]);

        return self::$user;
    }

    public function Logout(){
        self::$user= null;
        session_destroy();
        session_start();
    }

    /**
     * @param string $_userName
     * @param string $_password
     * @throws LoginException
     * @return LoggedUser
     */
    public function Login( $_userName, $_password ){
        global $DB;
        $_userName= strtolower($_userName);
        $passwordUnCrypted= $_password;
        $_password= self::CryptPassword( $_password );
        $query= $DB->query("select id, lang from ".TB_PREFIX."users 
            where (username= '$_userName' or email= '$_userName') and password= '$_password' limit 1;");
        if( $query->num_rows == 0 ) throw new LoginException("Invalid Login!");

        $aid= $query->fetch_array();
        $DB->query("update ".TB_PREFIX."users set last_log= NOW() 
            where id= ".(int)$aid["id"]);

        //if( $user->IsBanned() ) throw new LoginException("Banned user");
        self::$user= new LoggedUser((int)$aid["id"]);
        $_SESSION["userId"]= (int)$aid["id"];
		$_SESSION["language"]= $aid["lang"];

        foreach( self::$bridges as $br )
            $br->Login(self::$user->name, $passwordUnCrypted);
                
		return self::$user;
    }

    public function Register( $_userName, $_email, $_password, $_race, $_lang= "en", $_cityName= "", $initPos= NULL ){
        global $DB;

        if( !filter_var( $_email, FILTER_VALIDATE_EMAIL ) ) throw new RegistrationException("Invalid email");
        if( strlen( $_userName ) < 3 ) throw new RegistrationException("Username too short!");
        if( strlen( $_password ) < 3 ) throw new RegistrationException("Password too short!");

        $_userName= strtolower($_userName);
        $_email= strtolower($_email);
        $passwordUnCrypted= $_password;
        $_password= self::CryptPassword( $_password );
        if( $_cityName == null || $_cityName == "" ) $_cityName= "City of $_userName";

        $insertUserQr= $DB->query("INSERT INTO `".TB_PREFIX."users`
        (id, `username`, `password`, `race`, `capcity`, `email`, `timestamp_reg`, `rank`, `tut`, `lang`)
        VALUES (null, '$_userName', '$_password', $_race, null, '$_email', ".time().", 0, -1, '$_lang')");

        if( !$insertUserQr ) throw new RegistrationException("Username or Email already exist!");
        $id= $DB->insert_id;

        //set first user as admin
        if( $id == 1 ) $DB->query("update ".TB_PREFIX."users set rank= 3 where id= 1");

        $config= Config::Instance();
        switch(MAP_SYS){
            case 1: //ogame sys; generate coords for map sys 1
                do{
                    $x= mt_rand(0, $config->Map_max_x);
                    $y= mt_rand(0, $config->Map_max_y);
                    $z= mt_rand(1, $config->Map_max_z);

                    $pvc= $DB->query("SELECT * FROM ".TB_PREFIX."city WHERE x= $x AND y= $y AND z= $z")->num_rows;

                } while( $pvc != 0 );

                $cityId= City::CreateCity( $id, new MapCoordinates($x,$y,$z), $_cityName );
                break;
            case 2: //travian sys generate x,y
                $cityId= City::CreateCity( $id, City::GetMap2FreeCoords($initPos), $_cityName );
                break;
            /*case 3:
                //ikariam/grepolis sys
                //generate isle and isle position!
                $numisl= $DB->query("SELECT * FROM `".TB_PREFIX."isle`")->num_rows;

                $ipd[1]="a";
                $ipd[2]="b";
                $ipd[3]="c";
                $ipd[4]="d";
                do{
                    $islid=mt_rand(1,$numisl);
                    $islpos=mt_rand(1,4);
                    $vsplic= $DB->query("SELECT * FROM `".TB_PREFIX."isle` WHERE `id` =".$islid)->fetch_array();
                    $sicp=$vsplic['pos_'.$ipd[$islpos]];
                }while($sicp!=0);

                $cin="INSERT INTO ".TB_PREFIX."city (id, owner, name, last_update) VALUES (null, '$id', '$ncity', $startres '$mtimet')";
                $cityId= $DB->query($cin)->insert_id;

                $isi="UPDATE `".TB_PREFIX."isle` SET `pos_$islpos` = '$cidr' WHERE `id` =$islid LIMIT 1 ;";
                $DB->query($isi);
                break;*/
        }

        $DB->query("update ".TB_PREFIX."users set capcity= $cityId where id= $id");

        //insert resources
        $resd= $DB->query("SELECT * FROM `".TB_PREFIX."resdata`");
        while( $fres= $resd->fetch_array() ){
            $DB->query("INSERT INTO `".TB_PREFIX."city_resources` (`city_id`, `res_id`, `res_quantity`) VALUES ($cityId, {$fres['id']}, {$fres['start']});");
        }

        foreach (self::$bridges as $br) { /** @var ILoginBridge $br */
            $br->Register($_userName, $_email, $passwordUnCrypted, $_race, $_lang, null, null);
        }
        
        mail( $_email, "Registration to ".Config::Instance()->server_name, ParseBBCodes( Config::Instance()->registration_email, null, new User($id) ) );
    }

    public static function RecoverPassword( $_email ){
        global $DB, $config;
        $usrQr= $DB->query("select * from ".TB_PREFIX."users where email= '$_email'");
        if( $usrQr->num_rows ==0 ) throw new Exception("Invalid email!");

        $auser= $usrQr->fetch_array();
        $hash= md5($auser['id'].$auser['email'].time());
        $DB->query("insert into ".TB_PREFIX."user_passrecover values (".$auser['id'].", '$hash', ".(time() + 6000).")");
        mail($auser['email'], $config['server_name']." Password Recovery", "<a href='".$_SERVER['HOST']."?pg=register&uid=".$auser['id']."&resetpw=$hash'>Click Here to recover password</a>");
    }

    public static function ResetPassword( $_userId, $_newPassword, $_hash ){
        global $DB;

        $_newPassword= self::CryptPassword( $_newPassword );

        $qr= $DB->query("SELECT `until` FROM `".TB_PREFIX."user_passrecover` WHERE `usrid`= $_userId AND `hash`= '$_hash';");

        if( $qr->num_rows < 1 ) throw new Exception("Error");

        $DB->query("UPDATE `".TB_PREFIX."users` SET `password = '$_newPassword' WHERE `id`= $_userId;");
        $DB->query("DELETE FROM `".TB_PREFIX."user_passrecover` WHERE `usrid`= $_userId;");
    }
}

Auth::$_instance= new Auth();