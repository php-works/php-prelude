<?php

namespace Prelude\Database;

interface DBDeleteQuery {
    function where($whereClause, $bindings);

    function execute();
}
