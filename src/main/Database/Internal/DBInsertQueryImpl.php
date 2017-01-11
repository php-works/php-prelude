<?php

namespace Prelude\Database\Internal;

use Prelude\Database\DB;
use Prelude\Database\DBInsertQuery;

final class DBInsertQueryImpl implements DBInsertQuery {
    private $db;
    private $tableName;
    private $values;

    function __construct(DB $db, $tableName) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->values = null;
    }
    
    function values(array $values) {
        $ret = $this;
        
        if ($values !== $this->values) {
            $ret = clone $this;
            $ret->values = $values;
        }
        
        return $ret;
    }

    function execute() {
        if ($this->values !== null) {
            $tableName = $this->tableName;
            $columns = implode(',', array_keys($this->values));
            
            $placeholders = '';
            
            for ($i = 0; $i < count($this->values); ++$i) {
                if ($i === 0) {
                    $placeholders = '?';
                } else {
                    $placeholders .= ',?';
                }
            }
            
            $query = "insert into $tableName ($columns) values ($placeholders)";
            $bindings = array_values($this->values);

            $this->db
                ->query($query)
                ->bind($bindings)
                ->execute();
        }
    }
}
