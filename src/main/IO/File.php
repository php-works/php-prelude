<?php

namespace Prelude\IO;

interface File {
    function getPath();
    
    function geBaseName();
        
    function getParentFile();
    
    function getParentPath();
    
    function isFile();

    function isDir();

    function isLink();
    
    function getFileSize();
    
    function isAbsolute();

    function getCreationTime();
    
    function getLastModifiedTime();
    
    function getLastAccessTime();
    
    function getSecondsSinceCreation();

    function getSecondsSinceLastModified();
    
    function getSecondsSinceLastAccess();
}
