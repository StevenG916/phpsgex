<?php
namespace phpsgex\framework\models\buildings;

class MissingRequirementsException extends \Exception {

};

class BuildingRequirement {
    public $buildId, $level;

    public function __construct( $buildId, $level ){
        $this->buildId = $buildId;
        $this->level = $level;
    }
};