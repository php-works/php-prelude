<?php

namespace Prelude\IO;

use Closure;
use InvalidArgumentException;
use Prelude\Utility\Seq;

final class FileReader {
    private $openFile;

    private function __construct(Closure $openFile) {
        $this->openFile = $openFile;
    }

    public function open() {
        $openFile = $this->openFile;
        return $openFile();
    }

    function readFully(CharsetRecoder $charsetRecoder = null) {
        $ret = '';
        $stream = $this->open();

        try {        
            while (!feof($stream)) {
                $ret .= fread($stream, 8192);
            }
        } finally {        
            fclose($stream);
        }

        if ($charsetRecoder !== null) {
            $ret = $charsetRecoder->recodeString($ret);
        }

        return $ret;
    }
    
    function readSeq($separator = null, CharsetRecoder $charsetRecoder = null) {
        $openFile = $this->openFile;
        
        return Seq::from(function() use ($separator, $openFile, $charsetRecoder) {
            $stream = $openFile();
            
            try {
                while (true) {
                    $line =
                        $separator === null
                        ? @fgets($stream)
                        : @stream_get_line($stream, 1024, $separator);
                    
                    if ($line === false) {
                        break;
                    }
                    
                    
                    $length = strlen($line);
                    
                    while ($length > 0
                        && ($line[$length - 1] === "\r" || $line[$length -1] === "\n")) {
                    
                        --$length;
                    }
                    
                    $line = substr($line, 0, $length);

                    if ($charsetRecoder !== null) {
                        $line = $charsetRecoder->recodeString($line);
                    }
                    
                    yield $line;
                    
                }
                
                if (!feof($stream)) {
                    $message = error_get_last()['message'];
                    throw new IOException($message);
                }
            } finally {
                fclose($stream);
            }
        });
    }
    
    static function fromFile($file, $context = null) {
        $path = Files::getPath($file);
        
        $openFile = function () use ($path, $context) {
            return Files::openFile($path, 'rb', $context);
        };

        return new self($openFile);
    }
    
    
    // TODO: Is this really necessary?!?
    /*
    static function fromString($text) {
        $openFile = function () use ($text) {
            $stream = fopen('php://memory', 'wr');
            fwrite($stream, $text);
            rewind($stream);
            return $stream;
        };
        
        return new self($openFile);
    }
    */
}
