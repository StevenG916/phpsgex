<?php
use phpsgex\framework\models\User;
use phpsgex\framework\models\ally\Ally;
use phpsgex\framework\models\Config;
use phpsgex\framework\MessageType;

/**
 * @deprecated
 * @param $str
 * @return null|string
 */
function CleanString($str){
    global $DB;
    $r= $DB->escape_string( trim($str) );
    return ( strlen($r) ==0 ) ? null : $r;
}

/**
 * @deprecated
 * @param array $array
 */
function CleanArray( Array $array ){
    foreach( $array as $key => $value ){
        $array[$key]= CleanString($value);
    }
}

function str_startswith($source, $substr){
    return substr($source, 0, strlen($substr));
}

function sql_fld($cv){
    return empty($cv) ? "null" : ( is_numeric($cv) ? $cv : "'$cv'" );
}

function StringifyTime($t){
    return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
}

function ParseBBCodes($_body, User $sender= null, User $receiver= null){
    if( $sender != null ){
        $_body= preg_replace( '/\[my_name\]/', CleanString("<a href='?pg=profile&usr={$sender->id}'>{$sender->name}</a>"), $_body );
        if( $sender->allyId != null ) {
            $ally= new Ally($sender->allyId);
            $_body= preg_replace('/\[ally_name\]/', CleanString("<a href='?pg=ally&showally={$ally->id}'>{$ally->name}</a>"), $_body);
        }
    }
    if( $receiver != null ){
        $_body= preg_replace( '/\[your_name\]/', CleanString("<a href='?pg=profile&usr={$receiver->id}'>{$receiver->name}</a>"), $_body );
    }
    $_body= preg_replace( '/\[game_name\]/', Config::Instance()->server_name, $_body );

    return $_body;
}

function SendMessage( User $from= null, $_to, $_tittle, $_body, $_messageType= 1, Ally $allyInvite= null ){
    global $DB;

    if( $_messageType != MessageType::Report ){
        if( $from == null ) throw new Exception("from is null!");
        $fromid= $from->id;
    } else $fromid= "NULL";

    if( $_messageType == MessageType::AllyInvite ){
        if($allyInvite == null) throw new Exception("Message is an ally invite but ally is null");
        $allyId= $allyInvite->id;
    } else $allyId= "NULL";

    $receiver= User::Instantiate($_to);
    $_body= ParseBBCodes($_body, $from, $receiver);

    $DB->query("INSERT INTO `".TB_PREFIX."user_message` (`id` ,`from` ,`to` ,`mtit` ,`text` ,`read` ,`mtype` ,`aiid`) VALUES 
                    (NULL, $fromid, $_to, '$_tittle', '$_body', 0, $_messageType, $allyId);");
}

/**
 * @deprecated needs to be substituted by an Enum
 * @return array
 */
function bud_func(){ //show buildings func
    $bf= Array( "none", "barracks", "reslab", "buildfaster", "wall" );

    if( Config::Instance()->MG_max_cap > 0 ) $bf[]="mag_e";
    if( Config::Instance()->popres != null ) $bf[]="pop_e";
    return $bf;
}

/**
 * @param array $a
 * @return array
 */
function array_clone(Array $a){
    $new= array();
    foreach ($a as $k => $v) {
        $new[$k]= clone $v;
    }
    return $new;
}

function GetLanguages(){
    $ret= Array();
    foreach( scandir(__ROOT__."languages") as $file ){
        if( strpos($file,".php") )
            $ret[]= str_replace(".php", "", $file);
    }
    return $ret;
}

function getIP(){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } 
    return $_SERVER['REMOTE_ADDR'];
}

function multi_submit($type="POST"){
    // MAKE THE FUNCTION WORK FOR EITHER GET OR POST SUBMITS
    $input_array = (strtoupper($type) == "GET") ? $_GET : $_POST;

    // GATHER THE CONTENTS OF ALL THE SUBMITTED FIELDS AND MAKE A MESSAGE DIGEST
    $string = NULL;
    foreach ($input_array as $val) {
        // CONCATENATE ALL SUBMITTED VALUES
        $string .= $val;
    }
    $string = md5($string);

    // IF THE SESSION VARIABLE IS NOT SET THIS IS NOT A MULTI-SUBMIT
    if (!isset($_SESSION["_multi_submit"])) {
        // SAVE THE SUBMITTED DATA MESSAGE DIGEST
        $_SESSION['_multi_submit'] = $string;
        return FALSE;
    }

    // IF THE SESSION DATA MATCHES THE MESSAGE DIGEST THIS IS A MULTI-SUBMIT
    if ($_SESSION['_multi_submit'] === $string) {
        return TRUE;
    }
    else {
        // SAVE THE MESSAGE DIGEST TO DETECT FUTURE MULTI-SUBMIT
        $_SESSION['_multi_submit'] = $string;
        return FALSE;
    }
}
