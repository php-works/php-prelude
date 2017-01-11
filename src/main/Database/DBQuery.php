<?php

namespace Prelude\Database;

interface DBQuery extends DBExecutor {
    function bind($params);
    
    function bindMany($bindings);

    function limit($n);

    function offset($n);
}
