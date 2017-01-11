<?php

namespace Prelude\Database\Internal;

use InvalidArgumentException;
use PDO;
use Prelude\Database\DB;
use Prelude\Database\DBException;
use Prelude\Database\DBQuery;
use Prelude\Database\Internal\DBSelectQueryImpl;
use Prelude\Database\Internal\Adapters\PDOSQLiteAdapter;
use Prelude\Database\Internal\Adapters\PDOMySQLAdapter;

class DBImpl implements DB {
    private $params;
    private $adapter;
    
    private static $registeredDBs = [];
    
    function __construct(array $params) {
        $this->params = $params;
        $dsn = $this->params['dsn'];
        
        if (substr($dsn, 0, 7) === 'sqlite:') {
            $this->adapter = new PDOSQLiteAdapter($params);
        } else if (substr($dsn, 0, 6) === 'mysql:') {
            $this->adapter = new PDOMySQLAdapter($params);
        } else {
            throw new DBException("Incompatible database DSN '$dsn'");
        }
    }
    
    function getParams() {
        return $this->params;
    }

    function query($query) {
        return new DBQueryImpl($this->adapter, $query);
    }
    
    function from($fromClause) {
        return new DBSelectQueryImpl($this->adapter, $fromClause);
    }
    
    function insertInto($tableName) {
        return new DBInsertQueryImpl($this, $tableName);
    }
    
    function update($tableName) {
        return new DBUpdateQueryImpl($this, $tableName);
    }
    
    function deleteFrom($tableName) {
        return new DBDeleteQueryImpl($this, $tableName);
    }

    function runTransaction(callable $transaction) {
        $this->adapter->runTransaction($transaction);
    }
    
    function runIsolated(callable $action) {
        $this->adapter->runIsolated($action);
    }
    
    function runIsolatedTransaction(callable $transaction) {
        $this->adapter->runIsolated(function () use ($transaction) {
            $this->runTransaction($transaction); 
        });
    }
}

