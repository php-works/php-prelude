<?php

namespace Prelude\Database;

use InvalidArgumentException;
use PDO;
use Prelude\Utility\Seq;
use Prelude\Utility\DynObject;


interface DB {
    function getParams();
    
    function query($query);
    
    function from($fromClause);
    
    function insertInto($tableName);
    
    function update($tableName);
    
    function deleteFrom($tableName);
    
    function runIsolated(callable $action);

    function runTransaction(callable $transaction);

    function runIsolatedTransaction(callable $transaction);
}
