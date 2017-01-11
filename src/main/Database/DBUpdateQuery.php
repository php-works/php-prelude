<?php

namespace Prelude\Database;

interface DBUpdateQuery {
    function where($whereClause, $bindings);
    
    function set(array $modifications);

    function execute();
}
