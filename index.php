<?php
namespace phpsgex;

use phpsgex\framework\Navigator;
use phpsgex\framework\Auth;
use phpsgex\includes\Plugin;
use phpsgex\includes\Updater;

//version info
define("DEBUG", 0);
define("DBVER", 149); define("CONFVER", 101);
define("SGEXVER", "3.0.0.0Alpha");
//common define
session_start();
define("DS", DIRECTORY_SEPARATOR);
define("__ROOT__", getcwd().DS);
date_default_timezone_set('Europe/Rome');
error_reporting(E_ALL ^ E_STRICT);
if( !DEBUG ){
    ini_set('display_errors', '0');
    ini_set("error_log", __ROOT__."framework/php-error.log");
}

//navigation
$strController= isset($_GET["pg"]) ? $_GET["pg"] : "Index";
$strAction= isset($_GET["act"]) ? $_GET["act"] : "Index";

if( !file_exists("config.php") ){ //phpsgex not installed
    require_once 'install/InstallerController.php'; //INSTALLER
    $c= new \InstallerController();
    if( !method_exists($c, $strAction) )
        $c->Index();
    else
        $c->{$strAction}();
    exit;
}

require_once("config.php");
require_once("includes/common.php");

$DB= new \mysqli( SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB );
if( $DB->connect_error ) die("Database connection error ({$DB->connect_errno}) ".$DB->connect_error);
//autoloader
spl_autoload_register( function($class) {
    if( class_exists($class, false) ) return;

    $class= str_replace("phpsgex\\", "", $class);
    $file= __ROOT__.str_replace("\\", DS, $class) .".php";
    if(file_exists($file)) {
        require_once $file;
    } else error_log("can't load $class file: $file");
} );
//updater
Updater::UpdateConfig();
Updater::UpdateDb();

Plugin::LoadAll();

$user= Auth::Instance()->GetUser();
if( $user != null ){ //logged user
    $user->FetchResearchQueue();
    $user->GetCurrentCity()->FetchAllQueue();
}

if( !isset($_SESSION["language"]) ) $_SESSION["language"]= LANG;
if( isset($_REQUEST["SetLanguage"]) ) $_SESSION["language"]= $_REQUEST["SetLanguage"];
require_once("languages/".$_SESSION["language"].".php");
Navigator::Navigate($strController, $strAction);