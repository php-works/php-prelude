<?php

// TODO: remove later!!!
error_reporting(E_ALL);

spl_autoload_register(function ($className) {
    if (substr($className, 0, 8) === 'Prelude\\') {
        $path = __DIR__ . '/src/main/'
            . str_replace('\\', '/', substr($className, 8))
            . '.php';
        
        
        require_once $path;
    }
});
