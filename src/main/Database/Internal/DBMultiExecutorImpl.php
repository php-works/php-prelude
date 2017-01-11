<?php

namespace Prelude\DB\Internal;

use Prelude\Database\DB;
use Prelude\Database\DBMultiExecutor;
use Prelude\Database\internal\DBAdapter;
use Prelude\Utility\Seq;

class DBMultiExecutorImpl implements DBMultiExecutor {
    private $adapter;
    private $query;
    private $bindings;
    private $forceTransaction;

    function __construct(
        DBAdapter $adapter,
        $query,
        $bindings) {
        
        $this->adapter = $adapter;
        $this->query = $query;
        $this->bindings = $bindings;
        $this->forceTransaction = false;
    }
    
    function forceTransaction($forceTransaction) {
        $ret = this;
        
        if ($forceTransaction !== $this->forceTransaction) {
            $ret = clone $this;
            $ret->forceTransaction = $forceTransaction;
        }

        return $ret;
    }

    function execute() {
        return $this->adapter->process(
            $this->query,
            Seq::from($this->bindings),
            $this->forceTransaction);
    }
}
