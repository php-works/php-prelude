<?php

namespace Prelude\Database;

use Exception;
use RuntimeException;
use InvalidArgumentException;

class DBException extends RuntimeException {
    function __construct($message, $code = null, Exception $previous = null) {
        if ($message !== null && !is_string($message)) {
            throw new InvalidArgumentException(
                '[DBException.__construct] First argument $message must be a string');
        } else if ($code !== null && !is_numeric($code) && !is_string($code)) {
            throw new InvalidArgumentException(
                '[DBException.__construct] Second argument $code must either '
                . 'be an integer or an string or null');
        }
        
        parent::__construct($message);
    }
}