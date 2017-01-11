<?php

namespace Prelude\Database;

use PDO;
use Prelude\Database\Internal\DBImpl;

final class DBManager {
    private static $registeredDBs = [];
    
    private function __construct() {
    }
    
    static function createDB($params) {
        return new DBImpl($params);
    }

    static function registerDB($alias, $dbOrParams) {
        $db =
            $dbOrParams instanceof DB
            ? $dbOrParams
            : self::createDB($dbOrParams);

        self::unregisterDB($alias);
        self::$registeredDBs[$alias] = $db;
    }
    
    static function unregisterDB($alias) {
        if (isset(self::$registeredDBs[$alias])) {
            $db = self::$registeredDBs[$alias];
            $db->connection = null;
            unset(self::$registeredDBs[$alias]);
        }
    }
    
    static function getDB($alias) {
        $ret = null;
    
        if (!isset(self::$registeredDBs[$alias])) {
            throw new DBException(
                "[DBManager.getDB] Database '$alias' is not registered!");
        } else {
            $ret = self::$registeredDBs[$alias];
        }
    
        return $ret;
    }
}
