<?php

namespace Prelude\Database\Internal\Adapters;

use Prelude\Database\Internal\AbstractPDOAdapter;
use Prelude\Database\Internal\DBUtils;

class PDOMySQLAdapter extends AbstractPDOAdapter {
    function limitQuery($query, $limit = null, $offset = 0) {
        return DBUtils::limitQueryByLimitClause($query, $limit, $offset);        
    }
}

