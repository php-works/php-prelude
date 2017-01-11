<?php

namespace Prelude\IO;

use InvalidArgumentException;

final class FileComparators {
    private function __construct() {
    }
    
    static function byPath($reversed = false) {
        if (!is_bool($reversed)) {
            throw new InvalidArgumentException(
                '[FileComparators.byPath] First argument $reversed '
                . 'must be an integer');
        }
        
        return function ($file1, $file2) use ($reversed) {
            $path1 = is_string($file1) ? $file1 : $file1->getPath();;
            $path2 = is_string($file2) ? $file2 : $file2->getPath();;
            
            if ($reversed) {
                $ret = strcasecmp($path2, $path1);
            } else {
                $ret = strcasecmp($path1, $path2);
            }
            
            return $ret;
        };
    }

    static function byBaseName($reversed = false) {
        if (!is_bool($reversed)) {
            throw new InvalidArgumentException(
                '[FileComparators.byBaseName] First argument $reversed '
                . 'must be an integer');
        }
        
        return function ($file1, $file2) use ($reversed) {
            $baseName1 = Files::getBaseName($file1);
            $baseName2 = Files::getBaseName($file2);
            
            if ($reversed) {
                $ret = strcasecmp($baseName2, $baseName1);
            } else {
                $ret = strcasecmp($baseName1, $baseName2);
            }
            
            return $ret;
        };
    }
    
    static function byFileSize($reversed = false) {
        if (!is_bool($reversed)) {
            throw new InvalidArgumentException(
                '[FileComparators.byFileSize] First argument $reversed '
                . 'must be an integer');
        }
        
        return function ($file1, $file2) use ($reversed) {
            $size1 = Files::getFileSize($file1);
            $size2 = Files::getFileSize($file2);
            
            if ($reversed) {
                $ret = $size1 < $size2;
            } else {
                $ret = $size1 > $size2;
            }
            
            return $ret;
        };
    }
}