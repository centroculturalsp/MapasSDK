<?php

namespace MapasSDK\Exceptions;

class Exception extends \Exception {
    
    public $curl = null;

    public function __construct(\Curl\Curl $curl, $message = null) {
        $this->curl = $curl;
        
        $code = $curl->error_code;

        if (is_null($message)) {
            $message = $curl->error_message;
        }

        parent::__construct($message, $code);
    }

}
