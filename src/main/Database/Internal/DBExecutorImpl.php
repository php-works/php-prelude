<?php

namespace Prelude\Database\Internal;

use PDO;
use Prelude\Database\DB;
use Prelude\Database\DBExecutor;
use Prelude\Database\internal\DBAdapter;
use Prelude\Utility\Seq;
use Prelude\Utility\DynObject;

class DBExecutorImpl implements DBExecutor {
    protected $db;
    protected $query;
    protected $bindings;
    protected $limit;
    protected $offset;

    function __construct(DBAdapter $adapter, $query, $bindings = null, $limit = null, $offset = null) {
        $this->adapter = $adapter;
        $this->query = $query;
        $this->bindings = $bindings;
        $this->limit = $limit;
        $this->offset = $offset;
    }
    
    function execute() {
        $this->fetchRec();
    }
    
    function fetchSingle() {
        $row = $this->fetchRow();
    
        return $row === null ? null : $row[0];
    }
    
    function fetchRow() {
        $rec = $this->fetchRec();
    
        return $rec = null ? null : array_values($rec);
    }
    
    function fetchRec() {
        $arr =
            $this->fetchSeqOfRecs()
                ->take(1)
                ->toArray();
        
        return count($arr) === 0 ? null : $arr[0];
    }
    
    function fetchDynObject(array $options = null) {
        $rec = $this->fetchRec();
    
        return $rec === null ? $rec : DynObject::from($rec, $options);
    }
    
    function fetchSingles() {
        return $this->fetchSeqOfSingles()->toArray();
    }
    
    function fetchRows() {
        return $this->fetchSeqOfRows()->toArray();
    }
    
    function fetchRecs() {
        return $this->fetchSeqOfRecs()->toArray();
    }
    
    function fetchDynObjects() {
        return $this->fetchSeqOfDynObjects()->toArray();
    }
    
    function fetchMap() {
        $ret = [];
        
        foreach ($this->fetchSeqOfRows() as $row) {
            $ret[$row[0]] = @$row[1];
        }
        
        return $ret;
    }
    
    function fetchSeqOfSingles() {
        return $this->fetchRows()->map(function ($row) {
            return $row[0]; 
        });
    }
    
    function fetchSeqOfRows() {
        return $this->fetchRecs()->map(function ($rec) {
            return array_values($rec); 
        });
    }
    
    function fetchSeqOfRecs() {
        return $this->adapter->fetch(
            $this->query,
            $this->bindings,
            $this->limit,
            $this->offset);
    }
    
    function fetchSeqOfDynObjects(array $options = null) {
        return $this->fetchSeqOfRecs()->map(function ($rec) use ($options) {
            return DynObject::from($rec, $options);
        });
    }
}
