<?php
namespace phpsgex\controllers\acp;

use phpsgex\controllers\BaseController;

class IndexController extends BaseController {
    public function __construct(){
        parent::__construct();
        $this->requiredRank= 3;
    }

    public function Index(){
        parent::Index("acp/index");
    }
}