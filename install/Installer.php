<?php

class Installer {
    private $dbHost, $dbUser, $dbPassword, $dbName, $tablePrefix, /* @mysqli */ $DB;

    public function Installer( $_dbHost, $_dbUser, $_dbPassword, $_dbName, $_tablePrefix = "" ){
        $this->dbHost = $_dbHost;
        $this->dbUser = $_dbUser;
        $this->dbPassword = $_dbPassword;
        $this->dbName = $_dbName;
        $this->tablePrefix = $_tablePrefix;
    }

    public function MakeConfigFile( $_language, $_mapSystem, $_citySystem = 1 ){
        $myFile = "../config.php";
        $fh = fopen($myFile, 'w') or die("can't open file. Set chmod to 777!");
        $text = file_get_contents("config.tpl");
        $text = preg_replace("'%SSERVER%'", $this->dbHost, $text);
        $text = preg_replace("'%SUSER%'", $this->dbUser, $text);
        $text = preg_replace("'%SPASS%'", $this->dbPassword, $text);
        $text = preg_replace("'%SDB%'", $this->dbName, $text);
        $text = preg_replace("'%PREFIX%'", $this->tablePrefix, $text);
        $text = preg_replace("'%CTS%'", $_citySystem, $text);
        $text = preg_replace("'%MAP%'", $_mapSystem, $text);
        $text = preg_replace("'%LANG%'", $_language, $text);
        fwrite($fh, $text);
        fclose($fh);
    }

    private function exception($msg){ //TODO replace
        throw new RuntimeException($msg);
    }

    private function Error($msg){
        if( file_exists("../config.php") )
            unlink("../config.php");

        /* @mysqli $this->DB */
        if( $this->DB != null ){
            if(!$this->DB->connect_errno)
               $this->DB->query("drop database ".$this->dbName);
        }

        $this->DB->close();
        $_SESSION["err"]= $msg;
        header("Location: index.php");
    }

    public function DbConnect(){
        $this->DB = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword);

        if( !$this->DB || $this->DB->connect_error != null )
            throw new RuntimeException($this->DB->connect_error);
    }

    public function InstallDataBase( $_serverName, $_gameDescription, $_mainDescription, $_template, $_mapSystem = 1, $_citySystem = 1 ){
		ini_set('memory_limit', '5120M');
		set_time_limit ( 0 );

        $this->DbConnect();
        if( !$this->DB->select_db( $this->dbName )  ){
            $this->DB->query("create database if not exists ".$this->dbName) or $this->exception("Failed creating new database: ".$this->DB->error);
            $this->DB->select_db( $this->dbName ) or $this->exception("Database doesn't exist: ".$this->dbName." Go back and create database");
        }

        $dbscript= file_get_contents("sql/phpsgex.sql");

        switch( (int) $_mapSystem ){
            case 1:
                $dbscript .= file_get_contents("sql/map1.sql");
                break;
            case 2:
                $dbscript .= file_get_contents("sql/map2.sql");
                break;
            default:
                $this->Error( "INVALID MAP SYSTEM" );
                break;
        }

        if( (int) $_citySystem ==2 ) $dbscript .= file_get_contents("sql/city_sys2.sql");


        $dbscript .= "UPDATE `%PREFIX%conf` SET `server_name` = '$_serverName', `server_desc_sub` = '$_gameDescription', `server_desc_main` = '$_mainDescription', `template` = '$_template' WHERE 1 LIMIT 1;";
        $dbscript= preg_replace("'%PREFIX%'", $this->tablePrefix, $dbscript);

        file_put_contents("manual_db.sql", $dbscript);

        $this->DB->multi_query($dbscript);
        $i=1;
        do {
            $result = $this->DB->use_result();
            echo $i++.") ".$this->DB->affected_rows.' row(s) affected';
            if( ($i -1) % 10 ) echo "<br>";
        } while($this->DB->next_result());

        if($this->DB->errno) die($this->DB->error);
    }
};

?>