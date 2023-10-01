<?php
namespace phpsgex\framework\models\buildings;

use phpsgex\framework\models\ModelLoader;

class BuildQueue extends ModelLoader{
    public $id, $city, $build, $level, $end;

    public function __construct(Array $row){
        parent::__construct($row);
    }
}