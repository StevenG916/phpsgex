<?php
namespace phpsgex\framework\models\research;

class ResearchRequirement {
    public $researchId, $level;

    public function __construct( $reasearchId, $level ){
        $this->researchId = $reasearchId;
        $this->level = $level;
    }
}