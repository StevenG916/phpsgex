<?php
namespace phpsgex\framework\models\research;

use phpsgex\framework\models\ModelLoader;

class ResearchQueue extends ModelLoader{
    public $id, $city, $res_id, $level, $end;

    public function __construct(Array $row){
        parent::__construct($row);
    }
}
