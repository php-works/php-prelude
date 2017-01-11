<?php

namespace Prelude\IO;

use InvalidArgumentException;
use Prelude\IO\Internal\FileImpl;
use Prelude\Utility\Seq;

final class Files {
    private function __construct() {
    }
    
    static function getFile($file) {
        $fileIsString = is_string($file);
        
        if (!$fileIsString && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files#getFile] First argument $file must either be a '
                . 'string or a File object');
        }
        
        $ret =
            $fileIsString
            ? new FileImpl($file)
            : $file;
        
        return $ret;
    }
    
    static function getPath($file) {
        $fileIsString = is_string($file);
        
        if (!$fileIsString && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files#getPath] First argument $file must either be a '
                . 'string or a File object');
        }

        return
            $fileIsString
            ? $file
            : $file->getPath();
    }
    
    static function makeDir($dir, $mode = 0777, $recursive = false, $context = null) {
        $path = self::getPath($dir);
        
        if ($mode === null) {
            $mode = 0777;
        }
        
        $result =
            $context === null
            ? @mkdir($path, $mode, $recursive)
            : @mkdir($path, $mode, $recursive, $context);
        
        if ($result === false) {
            throw new IOException("[Files.makeDir] Could not create directory '$path'");
        }
    }

    static function removeDir($dir) {
        $errMsg = '';

        if (file_exists($dir)) {
            if (is_file($dir) || is_link($dir)) {
                if (!unlink($dir)) {
                     $errMsg = "[Files.removeDir] Could not remove '$dir'!";
                }
            } else {
                foreach (scandir($dir) as $item) {
                    if ($item != '.' && $item != '..') {
                        @chmod($dir . "/" . $item, 0777);
                        self::removeDir($dir . "/" . $item);
                    }
                }

                if (!rmdir($dir)) {
                    $errMsg = "Could not remove directory '$dir'";
                }
            }

            if ($errMsg) {
                throw $exception;
            }
        }
    }
    
    static function scanDir($directory, $context = null) {
        $path = self::getPath($directory);
        
        $ret =
            $context === null
            ? @scandir($path, null)
            : @scandir($path, null, $context);

        if ($ret === false) {
            if (!self::isDir($path)) {
                throw new IOException(
                    "[Files.list] '$path' is either not a directory "
                    . 'or not readable');
            }
        }
        
        return $ret;
    }

    static function remove($file) {
        $ret = false;

        if (is_dir($file)) {
            throw new IOException("[Files.delete] '$file' is a directory");
        }

        if (is_file($file) || is_link($file)) {
            if (!unlink($file)) {
                throw new IOException("Could delete '$file'!");
            }

            $ret = true;
        }

        return $ret;
    }

    static function getBaseName($file) {
        $path =
            is_string($file)
            ? $file
            : $file->getPath();
        
        $ret = basename($path);
        
        if ($ret === '') {
            $ret = false;
        }
        
        return $ret;
    }

    static function getParentPath($file) {
        $path = is_string($file) ? $file : $file->getPath();
        
        $ret = false;
        $parentPath = basedir($path);
        
        if (strlen($parentPath) > 0) {
            $last = $parentName[-1];
            
            if ($last !== ':' && $last !== '/') {
                $ret = $parentPath;
            } else {
                
                
            }
        }
        
        return $ret;
    }
    
    static function getParentFile($file) {
        $parentPath = self::getParentPath($file);
        
        return $parentPath === false ? false : Files::getFile($parentPath);
    }
    
    static function isAbsolute($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.isAbsolute] First argument $file must be a string '
                . 'or a File object');
        }
        
        $ret = false;
        $path = is_string($file) ? $file : $file->getPath();
        $firstChar = isset($path[0]) ? $path[0] : '';
        $secondChar = isset($path[1]) ? $path[1] : '';
        
        if ($firstChar == '/' || $firstChar == '\\' || $secondChar == ':') {
            $ret = true;
        }
        
        return $ret;
    }
    
    static function combinePaths($path1, $path2, $useNativeDirectorySeparator = false) {            
        $ret = '';
        $path1 = self::normalizePath($path1, true);
        $path2 = self::normalizePath($path2, true);
        
        if ($path1 === '' || self::isAbsolute($path2)) {
            $ret = $path2;
        } elseif ($path2 === '') {
            $ret = $path1;
        } else {
            $lastChar = substr($path1, strlen($path1) - 1);
            
            if ($lastChar == '/') {
                $ret = $path1 . $path2;
            } else {
                $ret = "$path1/$path2";
            }
        }
        
        $ret = self::normalizePath($ret, $useNativeDirectorySeparator);
        return $ret;    
    }
    
    static function normalizePath($path, $useNativeDirectorySeparator = false) {
        $ret = '';
        $ret = trim($path); // TODO?
        $ret = str_replace('\\', '/', $ret);
        $ret = str_replace('/./', '/', $ret);
            
        if ($useNativeDirectorySeparator) {
            $ret = str_replace('/', DIRECTORY_SEPARATOR, $ret);
        }
        
        return $ret;
    }
    
    static function isFile($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.isFile] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        
        return is_file($path);
    }

    static function isDir($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.isDir] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        
        return is_dir($path);
    }

    static function isLink($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.isLink] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        
        return is_link($path);
    }
    
    static function getFileSize($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getFileSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        
        return filesize($path);
    }
    
    static function getCreationTime($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        return @filectime($path);
    }
    
    
    static function getLastModifiedTime($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        return @filemtime($path);
    }
    
    static function getLastAccessTime($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        return @fileatime($path);
    }
    
    static function getSecondsSinceCreation($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        $creationTime = $this->getCreationTime();
        
        return $creationTime === false ? false : now() -> $creationTime;
    }

    static function getSecondsSinceLastModified($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        $lastModifiedTime = $this->getCreationTime();
        
        return $lastModifiedTime === false ? false : now() -> $lastModifiedTime;
    }
    
    static function getSecondsSinceLastAccess($file) {
        if (!is_string($file) && !($file instanceof File)) {
            throw new InvalidArgumentException(
                '[Files.getSize] First argument $file must be a string '
                . 'or a File object');
        }
        
        $path = is_string($file) ? $file : $file->getPath();
        
        $lastAccessTime = $this->getLastAccessTime();
        
        return $lastAccessTime === false ? false : now() -> $lastAccessTime;
    }
    
    static function openFile($file, $openMode, $context = null) {
        $path = self::getPath($file);
        
        $stream =
            $context === null
            ? fopen($path, $openMode)
            : fopen($path, $openMode, null, $context);
        
        if ($stream === false) {
            throw new IOException("Could not open file '$path'");
        }
        
        return $stream;
    }
}
