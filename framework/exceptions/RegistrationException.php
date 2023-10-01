<?php
namespace phpsgex\framework\exceptions;

class RegistrationException extends \Exception {
    public function RegisterException( $_message ){
        parent::__construct($_message);
    }
};