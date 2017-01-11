<?php

namespace Prelude\Database\Internal;

use Prelude\Database\DB;
use Prelude\Database\DBDeleteQuery;

class DBDeleteQueryImpl implements DBDeleteQuery {
    private $db;
    private $tableName;
    private $condition;
    private $params;

    function __construct(DB $db, $tableName) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->condition = null;
        $this->params = null;
    }
    
    function where($condition, $params) {
        $ret = $this;
        
        if ($condition !== $this->condition || $params !== $this->params) {
            $ret = clone $this;
            
            $ret->condition = $condition;
            $ret->params = $params;
        }
        
        return $ret;
    }
    

    function execute() {
        $tableName = $this->tableName;
        $condition = $this->condition;
        $bindings = [];
        
        if (strpos($condition, '?') !== false && !is_array($this->params)) {
            $bindings[] = $this->params;
        } else {
            foreach (array_values($this->params) as $value) {
                $bindings[] = $value;
            }
        }
        
        $query = "delete from $tableName where $condition ";
        
        $this->db
            ->query($query)
            ->bind($bindings)
            ->execute();
    }
}
