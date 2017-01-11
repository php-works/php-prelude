<?php

namespace Prelude\Database\Internal;

use PDO;
use Prelude\Database\DB;
use Prelude\Database\DBQuery;
use Prelude\Database\internal\DBAdapter;
use Prelude\Utility\Seq;
use Prelude\Utility\DynObject;

class DBQueryImpl extends DBExecutorImpl implements DBQuery {
    function __construct(DBAdapter $adapter, $query) {
        parent::__construct($adapter, $query);
    }
    
    function limit($n) {
        $ret = clone $this;
        $ret->limit = $n;
        return $ret;
    }

    function offset($n) {
        $ret = clone $this;
        $ret->offset = $n;
        return $ret;
    }
    
    function bind($params) {
        return new DBExecutorImpl(
            $this->adapter,
            $this->query,
            $params,
            $this->limit,
            $this->offset);
    }
    
    function bindMany($bindings) {
        return new DBMultiExecutorImpl(
            $this->adapter,
            $this->query,
            $bindings,
            $this->limit,
            $this->offset);
    }
}
