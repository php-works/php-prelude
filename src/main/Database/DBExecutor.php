<?php

namespace Prelude\Database;

interface DBExecutor {
    function execute();

    function fetchSingle();

    function fetchRow();

    function fetchRec();

    function fetchDynObject();

    function fetchSingles();

    function fetchRows();

    function fetchRecs();

    function fetchMap();

    function fetchSeqOfSingles();

    function fetchSeqOfRows();

    function fetchSeqOfRecs();

    function fetchSeqOfDynObjects(array $options = null);
}
