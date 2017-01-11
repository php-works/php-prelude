<?php

namespace Prelude\IO;

use Closure;
use InvalidArgumentException;
use Prelude\Utility\Seq;

final class FileWriter {
    private $openFile;
    private $append;
    
    private function __construct(Closure $openFile) {
        $this->openFile = $openFile;
        $this->append = false;
    }
    
    function append($append = true) {
        $ret = $this;
        
        if ($this->append !== $append) {
            $ret = clone $this;
            $ret->append = $append;
            return $ret;
        }
        
        return $ret;
    }
    
    function open() {
        $openFile = $this->openFile;
        return $openFile($this);
    }
    
    function writeFully($text, CharsetRecoder $charsetRecoder = null) {
        if (!is_string($text)) {
            throw new InvalidArgumentException(
                '[FileWriter#writeFull] First argument $text must be a string');
        }
        
        $textToOutput = 
            $charsetRecoder !== null
            ? $charsetRecoder->recodeString($text)
            : $text;
            
        $length = strlen($textToOutput);
        $stream = $this->open();
        
        try {
            $result = fwrite($stream, $textToOutput, $length);
    
            if ($result === false || $result !== $length) {
                $message = error_get_last()['message'];
                throw new IOException($message);
            }
        } finally {
            fclose($stream);
        }
        
        return $length;
    }
    
    function writeSeq(
        Seq $seq,
        $separator = "\n",
        CharsetRecoder $charsetRecoder = null) {
        
        
        $itemCount = 0;
        $stream = $this->open();
        
        try {
            foreach ($seq as $item) {
                ++$itemCount;

                if ($charsetRecoder !== null) {
                    $item = $charsetRecoder->recodeString($item);
                }
                
                foreach ([$item, $separator] as $s) {
                    $result = fwrite($stream, $s);
                
                    if ($result === false) {
                        $message = error_get_last()['message'];
                        throw new IOException($message);
                    }
                }
            }
        } finally {
            fclose($stream);
        }
        
        return $itemCount;
    }
    
    static function fromFile($file, array $context = null) {
        $path = Files::getPath($file);
        
        $openFile = function ($FileWriter) use ($path, $context) {
            $openMode =
                $FileWriter->append
                ? 'ab'
                : 'wb';
            
            return Files::openFile($path, $openMode, $context);
        };
        
        return new self($openFile);
    }
}
