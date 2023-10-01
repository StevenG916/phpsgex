<?php
namespace phpsgex\framework\models;

final class Config extends ModelLoader{
    public $news1, $registration_email, $rulers, $server_name, $server_desc_sub, $server_desc_main, $template,
        $css, $popres, $battleSys, $MG_max_cap, $MG_mul, $Map_max_x, $Map_max_y, $Map_max_z, $FLAG_SZERORES,
        $FLAG_SUNAVALB, $FLAG_RESICONS, $FLAG_SHOWUSRMAIL, $cusr_pics, $baru_tmdl, $unit_que_max, $unit_que_parallel,
        $research_que_max, $build_que_max, $build_que_parallel, $buildfast_molt, $researchfast_molt, $serverEnd, $sge_ver;

    public static $instance= null;

    /**
     * @return Config
     */
    public static function Instance(){
        if(self::$instance==null) self::$instance= new Config();

        return self::$instance;
    }

    protected function __construct(){
        global $DB;
        $aqr= $DB->query("select * from ".TB_PREFIX."conf limit 1")->fetch_array();

        parent::__construct($aqr);
    }
}