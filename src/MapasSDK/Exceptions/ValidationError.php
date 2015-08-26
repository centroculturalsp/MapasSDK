<?php

namespace MapasSDK\Exceptions;

class ValidationError extends Exception {
    public function __construct(\Curl\Curl $curl, $message = null) {
        parent::__construct($curl, $message);
    }
}
