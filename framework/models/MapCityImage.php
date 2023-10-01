<?php
namespace phpsgex\framework\models;

class MapCityImage extends ModelLoader {
    public $id, $points= 0, $image, $abbandoned= false, $bonus= false;
    
    public function __construct(array $values) {
        parent::__construct($values);
    }
    
    public static function GetImage($points, $bonus= false, $abbandoned= false){
        global $DB;
        
        if($points==null) $points= 0;
        
        $qr= $DB->query("select image from ".TB_PREFIX."mapcityimage "
                . "     where points= (select max(points) from ".TB_PREFIX."mapcityimage "
                . "                     where points <= $points)
                        and abbandoned= ".($abbandoned? "true" : "false")."
                        and bonus= ".($bonus? "true" : "false"));

        if($DB->error)
            error_log("Database error: ".$DB->error);

        if($qr->num_rows ==0){
            error_log(__CLASS__.": Can't find image ($points, $bonus, $abbandoned)!");
            return "v1";
        }

        return $qr->fetch_array()[0];
    }
}
