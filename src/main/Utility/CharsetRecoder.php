<?php

namespace Prelude\Utility;

use \InvalidArgumentException;

final class CharsetRecoder {
    private $sourceCharset;
    private $targetCharset;

    private function __construct($sourceCharset, $targetCharset) {
        if (!is_string($sourceCharset)) {
            throw new InvalidArgumentException(
                '[CharsetRecoder::create] First argument $sourceCharset '
                . 'must be a string');
        } else if (!is_string($targetCharset)) {
            throw new InvalidArgumentException(
                '[CharsetRecoder::create] Two argument $targetCharset '
                . 'must be a string');
        }
        
        $this->sourceCharset = $sourceCharset;
        $this->targetCharset = $targetCharset;
    }
    
    function getSourceCharset() {
        return $this->sourceCharset;
    }
    
    function getTargetCharset() {
        return $this->targetCharset;
    }
    
    function recodeString($s) {
        $ret =
            iconv(
                $this->sourceCharset,
                $this->targetCharset . '//TRANSLIT',
                $s);
    
        if (strtolower($this->sourceCharset) == 'utf-8'
            && strtolower($this->targetCharset) == 'utf-8') {
            
            $ret = preg_replace('/\p{Cc}/u', '', $ret);
        }
    
        return $ret;
    }
    
    static function create($sourceCharset, $targetCharset) {
        return new self($sourceCharset, $targetCharset);
    }
}

