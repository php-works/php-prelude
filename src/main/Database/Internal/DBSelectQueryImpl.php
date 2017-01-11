<?php

namespace Prelude\Database\Internal;

use Prelude\Database\DBException;
use Prelude\Database\DBSelectQuery;

final class DBSelectQueryImpl extends DBExecutorImpl implements DBSelectQuery {
    private $from;
    private $select;
    private $selectBindings;
    private $where;
    private $whereBindings;
    private $groupBy;
    private $groupByBindings;
    private $having;
    private $havingBindings;
    private $orderBy;
    private $oderByBindings;

    function __construct(DBAdapter $adapter, $fromClause) {
        $from = trim($fromClause);
        $query = 'select * from ' . $from;

        parent::__construct($adapter, $query);
        
        $this->adapter = $adapter;
        $this->from = trim($fromClause);
        $this->select = '';
        $this->where = '';
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
        
        $this->selectBindings = null;
        $this->whereBindings = null;
        $this->groupByBindings = null;
        $this->havingBindings = null;
        $this->orderByBindings = null;
    }
    
    function select($selectClause, $bindings = null) {
        $ret = $this;
        $select = trim($selectClause);
        $this->checkClauseBindings('select', $select, $bindings);
        
        if ($select !== $this->select || $bindings = $this->selectBindings) {
            $ret = clone $this;
            $ret->select = $select;
            $ret->selectBindings = $bindings;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    function where($whereClause, $bindings = null) {
        $ret = $this;
        $where = trim($whereClause);
        $this->checkClauseBindings('where', $where, $bindings);

        
        if ($where !== $this->where || $bindings !== $this->whereBindings) {
            $ret = clone $this;
            $ret->where = $where;
            $ret->whereBindings = $bindings;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    function groupBy($groupByClause, $bindings = null) {
        $ret = $this;
        $groupBy = trim($groupByClause);
        $this->checkClauseBindings('groupBy', $groupBy, $bindings);

        
        if ($groupBy !== $this->groupBy || $bindings !== $this->groupByBindings) {
            $ret = clone $this;
            $ret->groupBy = $groupBy;
            $ret->groupByBindings = $groupByBindings;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    function having($havingClause, $bindings = null) {
        $ret = $this;
        $having = trim($havingClause);
        $this->checkClauseBindings('having', $having, $bindings);

        
        if ($having !== $this->having || $bindings !== $this->havingBindings) {
            $ret = clone $this;
            $ret->having = $having;
            $ret->havingBindings = $bindings;
            $ret->updateQueryAndBindings();
        }
    }
    
    function orderBy($orderByClause, $bindings = null) {
        $ret = $this;
        $orderBy = trim($orderByClause);
        $this->checkClauseBindings('orderBy', $orderBy, $bindings);

        
        if ($orderBy !== $this->orderBy || $bindings !== $this->orderByBindings) {
            $ret = clone $this;
            $ret->orderBy = $orderBy;
            $ret->orderByBindings = $bindings;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    function limit($limit) {
        $ret = $this;
    
        if ($limit !== $this->limit) {
            $ret = clone $this;
            $ret->limit = $limit;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    function offset($offset) {
        $ret = $this;
        
        if ($offset !== $this->offset) {
            $ret = clone $this;
            $ret->offset = $offset;
            $ret->updateQueryAndBindings();
        }
        
        return $ret;
    }
    
    private function checkClauseBindings($clauseName, $clause, $bindings) {
        if (strpos($clause, ':') !== false) {
            throw new DBException(
                "It's not allowed that $clauseName clause "
                . 'passed to the query builder contains a colon');
        } else if (strpos($clause, '??') !== false) {
            throw new DBException(
                "It's not allowed that $clauseName clause "
                . "passed to the query builder contains a '??'");
        }
        
        $questionMarkCount = substr_count($clause, '?');
        $bindingCount = is_array($bindings) ? count($bindings) : 1;
        $diffCount = $bindingCount - $questionMarkCount;
        
        if ($diffCount !== 0  && ($diffCount !== 1 || $bindings !== null)) {
            throw new DBException(
                'The numbers of bindings and question mark placeholders '
                . "for '$clauseName' clause differ");
        }
    }
    
    private function validateBindings($bindings, $clauseName) {
        if (strpos($clauseName, ':') !== false) {
            throw new DBException("Invalid binding for $clauseName clause does contain a colon");
        }
        
    }
    
    private function updateQueryAndBindings() {
        $query = $this->from;
        $bindings = [];
        
        if ($this->select !== '') {
            $clauseNames = ['select', 'where', 'groupBy', 'having', 'orderBy'];
            
            foreach ($clauseNames as $clauseName) {
                $clause = $this->$clauseName;
                
                if ($clauseName === 'select') {
                    if ($this->select === '') {
                        $query = 'select * from ' . $this->from;
                    } else {
                        $query = "select $clause from " . $this->from;
                    }
                } else if ($clause !== '') {
                    $query .= ' ';
                    $query .= str_replace('By', ' by', $clauseName);
                    $query .= " $clause";
                }
                
                $clauseBindings = $this->{$clauseName . 'Bindings'};
                $questionMarkCount = substr_count($clause, '?');
                
                if ($questionMarkCount > 0) {
                    if (!is_array($clauseBindings) && $questionMarkCount === 1) {
                        $bindings[] = $clauseBindings;
                    } else if (is_array($clauseBindings)) {
                        foreach ($clauseBindings as $clauseBinding) {
                            $bindings[] = $clauseBinding;
                        }
                    }
                }
            }
        }
        
        $this->query = $query;
        $this->bindings = $bindings;
    }
}
