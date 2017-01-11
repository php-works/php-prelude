<?php

namespace Prelude\Database\Internal;

use Prelude\Database\DB;
use Prelude\Database\DBUpdateQuery;

class DBUpdateQueryImpl implements DBUpdateQuery {
    private $db;
    private $tableName;
    private $condition;
    private $params;
    private $modifications;
    
    function __construct(DB $db, $tableName) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->condition = null;
        $this->params = null;
        $this->modifications = null;
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
    
    function set(array $modifications) {
        $ret = $this;
        
        if ($modifications !== $this->modifications) {
            $ret = clone $this;
            $ret->modifications = $modifications;
        }
        
        return $ret;
    }

    function execute() {
        $tableName = $this->tableName;
        $condition = $this->condition;
        $bindings = [];
        
        
        $setClause = '';
        
        foreach ($this->modifications as $key => $value) {
            if ($setClause === '') {
                $setClause = 'set ';
            } else {
                $setClause .= ',';
            }
            
            $setClause .= "$key=?";
            $bindings[] = $value;
        }
        
        if (strpos($condition, '?') !== false && !is_array($this->params)) {
            $bindings[] = $this->params;
        } else {
            foreach (array_values($this->params) as $value) {
                $bindings[] = $value;
            }
        }
        
        $query = "update $tableName $setClause where $condition ";
        
        $this->db
            ->query($query)
            ->bind($bindings)
            ->execute();
    }
}
