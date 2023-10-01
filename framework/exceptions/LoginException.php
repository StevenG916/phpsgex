<?php
namespace phpsgex\framework\exceptions;

class LoginException extends \Exception {
    public function LoginException( $_message ){
        parent::__construct($_message);
    }
}