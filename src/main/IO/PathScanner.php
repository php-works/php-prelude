<?php

namespace Prelude\IO;

use InvalidArgumentException;
use Prelude\Utility\Seq;

final class PathScanner {
    private $recursive;
    private $listPaths;
    private $forceAbsolute;
    private $sort;
    private $fileIncludeFilter;
    private $fileExcludeFilter;
    private $dirIncludeFilter;
    private $dirExcludeFilter;
    private $linkIncludeFilter;
    private $linkExcludeFilter;

    private function __construct() {
        $defaultFilter = self::createFilter(false);
        
        $this->recursive = false;
        $this->listPaths = false;
        $this->forceAbsolute = false;
        $this->sort = null;
        $this->fileIncludeFilter = $defaultFilter;
        $this->fileExcludeFilter = $defaultFilter;
        $this->dirIncludeFilter = $defaultFilter;
        $this->dirExcludeFilter = $defaultFilter;
        $this->linkIncludeFilter = $defaultFilter;
        $this->linkExcludeFilter = $defaultFilter;
        $this->forceAbsolute = false;
     }
    
    function recursive($recursive = true) {
        if (!is_bool($recursive)) {
            throw new InvalidArgumentException(
                '[PathScanner#recursive] First argument $recursive must be boolean');
        }
        
        $ret = clone $this;
        $ret->recursive = $recursive;
        return $ret;
    }

    function forceAbsolute($forceAbsolute = true) {
        if (!is_bool($forceAbsolute)) {
            throw new InvalidArgumentException(
                '[PathScanner#forceAbsolute] First argument $forceAbsolute must be boolean');
        }
        
        $ret = clone $this;
        $ret->forceAbsolute = $forceAbsolute;
        return $ret;
    }

    function listPaths($listPaths = true) {
        if (!is_bool($listPaths)) {
            throw new InvalidArgumentException(
                '[PathScanner#listPaths] First argument $listPaths must be boolean');
        }
        
        $ret = clone $this;
        $ret->listPaths = $listPaths;
        return $ret;
    }

    function includeFiles($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#includeFiles] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->fileIncludeFilter = self::createFilter($select);
        return $ret;
    }

    function excludeFiles($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#excludeFiles] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->fileExcludeFilter = self::createFilter($select);
        return $ret;
    }

    function includeDirs($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#includeDirs] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->dirIncludeFilter = self::createFilter($select);
        return $ret;
    }

    function excludeDirs($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#excludeDirs] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->dirExcludeFilter = self::createFilter($select);
        return $ret;
    }

    function includeLinks($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#includeLinks] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->linkIncludeFilter = $select;
        return $ret;
    }

    function excludeLinks($select = true) {
        if (!self::isValidSelectArgument($select)) {
            throw new InvalidArgumentException(
                '[PathScanner#excludeLinks] First argument $select must '
                . 'either be boolean or a callable or a string or an array '
                . 'of strings and/or callables');
        }

        $ret = clone $this;
        $ret->linkExcludeFilter = self::createFilter($select);
        return $ret;
    }

    function sort(callable $compare = null) {
        $ret = clone $this;
        $ret->sort = $compare;
        return $ret;
    }

    function scan($dir, $context = null) {
         if (!is_string($dir) && !($dir instanceof File)) {
            throw new InvalidArgumentException(
                '[PathScanner#scan] First argument $dir must be a string or a File object');
        }
        
        $ret = null;

        if ($this->listPaths) {
            $ret =
                $this
                    ->listPaths(false)
                    ->scan($dir, $context)
                    ->map(function ($file) {
                        return $file->getPath();
                    });
        } else {
            $parentPath =
                is_string($dir)
                ? $dir
                : $dir->getPath();
    
            $ret = Seq::from(function () use ($dir, $context, $parentPath) {
                $items = Files::scanDir($parentPath, $context);
                
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    
                    $path = Files::combinePaths($parentPath, $item);
                    
                    if ($this->forceAbsolute && !Files::isAbsolute($path)) {
                        $path = Files::combinePaths(getcwd(), $path);
                    }
                  
                    $file = Files::getFile($path);
                    
                    if ($this->fileIsIncluded($file)) {
                        yield $file;
                    }
                    
                    if ($this->recursive && $file->isDir()) {
                        $subitems = $this->scan($path, $context);
    
                        foreach ($subitems as $subitem) {
                            yield $subitem;
                        }
                    }
                }
            });
            
            if ($this->sort !== null) {
                $ret = $ret->sort($this->sort);
            }
        }
        
        return $ret;
    }
    
    static function create() {
        return new self();
    }
    
    private static function isValidSelectArgument($select) {
        $ret = false;
        
        if (is_bool($select)) {
            $ret = true;
        } else if (is_string($select)) {
            $ret = true;
        } else if (is_callable($select)) {
            $ret = true;
        } else if (is_array($select)) {
            $ret = true;
            
            foreach ($select as $constraint) {
                if (!is_string($constraint) && !is_callable($constraint)) {
                    $ret = false;
                    break;
                }
            }
        }
        
        return $ret;
    }
    
    private static function createFilter($select) {
        $ret = null;
        
        if (is_bool($select)) {
            $ret = function () use ($select) {
                return $select;
            };
        } else if (is_string($select)) {
            $ret = function ($file) use ($select) {
                return fnmatch($select, $file->getPath());
            };
        } else if (is_callable($select)) {
            $ret = $select;
        } else if (is_array($select)) {
            $ret = function ($file) use ($select) {
                $result = false;
                
                foreach ($select as $constraint) {
                    if (is_callable($constraint)) {
                        if ($constraint($file)) {
                            $result = true;
                            break;
                        }
                    } else if (is_string($constraint)) {
                        if (fnmatch($constraint, $file->getPath())) {
                            $result = true;
                            break;
                        }       
                    }
                }
                
                return $result;
            };
        } else {
            throw new Exception("[PathScanner#createFilter] This case should never happen");
        }
        
        return $ret;
    }
    
    private function fileIsIncluded($file) {
        $ret = false;
        $isFile = $file->isFile();
        $isDir = !$isFile && $file->isDir();
        $isLink = !$isFile && !$isDir && $file->isLink();
        
        if ($isFile) {
            $ret = $this->fileIncludeFilter->__invoke($file)
                && !$this->fileExcludeFilter->__invoke($file);
        }
        
        if ($isDir) {
            $ret = $this->dirIncludeFilter->__invoke($file)
                && !$this->dirExcludeFilter->__invoke($file);
        }
        
        if ($isLink) {
            if ($ret) {
                $ret = !$this->linkExcludeFilter->__invoke($file);
            } else {
                $ret = $this->linkIncludeFilter->__invoke($file)
                    && !$this->linkExcludeFilter->__invoke($file);
            }
        }
    
        return $ret;                        
    }
}