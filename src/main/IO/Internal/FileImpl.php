<?php

namespace Prelude\IO\Internal;

use InvalidArgumentException;
use Prelude\IO\File;
use Prelude\IO\Files;

final class FileImpl implements File {
    private $path;
    
    public function __construct($path) {
        $this->path = $path;
    }
    
    function getPath() {
        return $this->path;
    }
    
    function geBaseName() {
        return Files::getBaseName($this->path);
    }
    
    function getParentFile() {
        return Files::getParentFile($this->path);
    }
    
    function getParentPath() {
        return Files::getParentPath($this->path);
    }
    
    function isFile() {
        return Files::isFile($this->path);
    }

    function isDir() {
        return Files::isDir($this->path);
    }

    function isLink() {
        return Files::isLink($this->path);
    }
    
    function getFileSize() {
        return Files::getFileSize($this->path);
    }
    
    function isAbsolute() {
        return Files::isAbsolute($this->path);
    }

    function getCreationTime() {
        return Files::getCreationTime($this->path);
    }
    
    function getLastModifiedTime() {
        return Files::getLastModifiedTime($this->path);
    }
    
    function getLastAccessTime() {
        return Files::getLastAccessTime($this->path);
    }
    
    function getSecondsSinceCreation() {
        return Files::getSecondsSinceCreation($this->path);
    }

    function getSecondsSinceLastModified() {
        return Files::getSecondsSinceLastModified($this->path);
    }
    
    function getSecondsSinceLastAccess() {
        return Files::getSecondsSinceLastAccess($this->path);
    }
    
    static function from($path) {
         if (!is_string($path) && !($path instanceof self)) {
            throw new InvalidArgumentException(
                '[File.from] First argument $path must be a string');
        }
        
        return new self($path);
    }
}
