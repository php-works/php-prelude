<?php

namespace Prelude\Database\Internal;

class DBUtils {
    private function __construct() {
    }
    
    static function limitQueryByLimitClause($query, $limit = null, $offset = 0) {
        $qry = trim($query);
        $ret = $qry;
        
        if ($limit !== null || $offset > 0) {
            if ($limit === null) {
                // TODO
                $limit = "2000000000";
            } elseif ($limit <= 0) {
                $limit = 0;
            }
      
            $offset = max(0, (int)$offset);
      
           $qryLower = strtolower($qry);
      
            if (strpos($qryLower, 'limit') === false && strpos($qryLower, 'union') === false) {
                $ret = "$qry\nlimit $limit offset $offset";
            } else {
                $ret = "select ___.*\nfrom(\n$qry\n) as ___\nlimit $limit offset $offset";
            }
        }

        return $ret;
    }    
}