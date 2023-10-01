<?php
namespace phpsgex\framework\exceptions;

class NameAlreadyInUseException extends \Exception {
    function __construct($msg=""){
        parent::__construct($msg);
    }
}