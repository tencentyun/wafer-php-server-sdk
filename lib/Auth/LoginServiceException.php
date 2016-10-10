<?php
namespace QCloud_WeApp_SDK\Auth;

use \Exception as Exception;

class LoginServiceException extends Exception {
    protected $type;

    public function __construct($type, $message, $code = 0, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    final public function getType() {
        return $this->type;
    }
}
