<?php
namespace phpsgex\includes;

abstract class Plugin{
    const frameworkVersion= 3;
    public $name, $displayName, $author, $webSite, $version= 0, $enabled= false, $error;

    private static $plugins= Array();

    private static function GetAllPluginsNames(){
        $ret= array();
        $files= scandir( __ROOT__."plugins" );
        foreach( $files as $dir ){
            if( !is_dir( __ROOT__."plugins/".$dir ) || $dir == "." || $dir == ".." ) continue;

            $ret[]= $dir;
        }
        return $ret;
    }

    public static function GetLoadedPlugins(){
        return self::$plugins;
    }

    public static function LoadAll(){
        foreach( self::GetAllPluginsNames() as $p ){
            $pluginfile= __ROOT__."plugins/$p/index.php";
            if( !file_exists($pluginfile) ) continue;

            require_once($pluginfile);
            $ref= new \ReflectionClass( "phpsgex\\plugins\\$p\\$p" );
            $plug= $ref->newInstance(); /** @var Plugin $plug */

            self::$plugins[]= $plug;
            if( $plug->enabled && !$plug->error ){
                try {
                    $plug->Activate();
                } catch(\Exception $ex){
                    $plug->error = $ex->getMessage();
                    error_log($ex->getTraceAsString());
                }
            }
        }
    }

    protected abstract function Activate();

    public function __construct($importSql= null){
        $this->name= str_replace("\\", "\\\\", get_class($this) );
        global $DB;
        $qr= $DB->query("select * from ".TB_PREFIX."plugins where name= '{$this->name}' limit 1");

        if($qr->num_rows ==0)
            $this->Install($importSql);
        else {
            $aqr= $qr->fetch_array();
            $this->displayName= $aqr["name"];
            $this->enabled= (bool)$aqr["active"];
        }
    }

    public function Install($importSql= null){
        global $DB;
        $DB->query("insert into ".TB_PREFIX."plugins values ( '{$this->name}', false )");

        if( $importSql ==null ) return;

        $sql= file_get_contents($importSql);
        $sql= preg_replace("%PREFIX%", TB_PREFIX, $sql);
        $DB->multi_query($sql);
    }

    public function Uninstall(){
        global $DB;
        $DB->query("delete from ".TB_PREFIX."plugins where name= '{$this->name}'");
    }
}