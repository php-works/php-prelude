<?php

namespace Prelude\IO;

use Exception;
use IllegalArgumentException;

class IOException extends Exception {
    function __construct($message) {
        if (!is_string($message)) {
            throw new IllegalArgumentException(
                '[IOException.__construct] First argument $message must be a string');
        }
        
        parent::__construct($message);
    }
}