<?php
class InstallerController {
    public static function PhpOk(){ return PHP_VERSION_ID >= 50302; }
    
    public function Index(){
        require_once __ROOT__.'includes/common.php';
        //language
        if( !isset($_SESSION['lang']) ) $_SESSION["lang"]= "en";
        if( isset($_REQUEST["language"]) ) $_SESSION["lang"]= $_REQUEST["language"];
        require_once __ROOT__."languages/{$_SESSION["lang"]}.php";
        
        require_once __ROOT__."install/step1.php";
    }

    public function Install(){
        if( !isset($_POST["dbHost"]) || file_exists("config.php") ){
            header("Location: index.php");
            return;
        }

        $installer= new Installer($_POST["dbHost"], $_POST["dbUser"], $_POST["dbPassword"], $_POST["dbName"], $_POST["dbTablePrefix"]);
        try{
            $installer->DbConnect();
        }catch(RuntimeException $ex){
            $_SESSION["err"]= $ex->getMessage();
            header("Location: index.php");
            return;
        }
    }
}
