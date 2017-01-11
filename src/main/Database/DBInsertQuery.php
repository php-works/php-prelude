<?php

namespace Prelude\Database;

interface DBInsertQuery {
    function values(array $values);

    function execute();
}
