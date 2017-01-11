<?php

namespace Prelude\Database;

interface DBMultiExecutor {
    function forceTransaction($forceTransaction);

    function execute();
}
