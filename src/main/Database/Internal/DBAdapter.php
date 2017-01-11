<?php

namespace Prelude\Database\Internal;

use Prelude\Utility\Seq;

interface DBAdapter {
    function process($query, Seq $bindings, $forceTransaction);
    function fetch($query, $bindings = null, $limit = null, $offset = 0);
    function runTransaction(callable $transaction);
    function runIsolated(callable $action);
}
