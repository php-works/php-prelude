<?php

namespace Prelude\CSV;

final class CSVFormat {
    private $columns;
    private $delimiter;
    private $escapeChar;
    private $quoteChar;
    private $suppressHeader;
    private $autoTrim;
    
    private function __construct() {
        $this->columns = null;
        $this->delimiter = ',';
        $this->escapeChar = '\\';
        $this->quoteChar = '"';
        $this->suppressHeader = false; 
        $this->autoTrim = false;
    }
    
    function columns(array $columns) {
        $ret = clone $this;
        $ret->columns = $columns;
        return $ret;
    }

    function delimiter($delimiter) {
        $ret = clone $this;
        $ret->delimiter = $delimiter;
        return $ret;
    }

    function escapeChar($escapeChar) {
        $ret = clone $this;
        $ret->escapeChar = $escapeChar;
        return $ret;
    }

    function quoteChar($quoteChar) {
        $ret = clone $this;
        $ret->quoteChar = $quoteChar;
        return $ret;
    }
    
    function suppressHeader($suppressHeader = true) {
        $ret = clone $this;
        $ret->suppressHeader = $suppressHeader;
        return $ret;
    }

    function autoTrim($autoTrim) {
        $ret = clone $this;
        $ret->autoTrim = $autoTrim;
        return $ret;
    }
    
    function getParams() {
        return [
            'columns' => $this->columns,
            'delimiter' => $this->delimiter,
            'escapeChar'=> $this->escapeChar,
            'quoteChar' => $this->quoteChar,
            'suppressHeader' => $this->suppressHeader,
            'autoTrim' => $this->autoTrim
        ];
    }
    
    static function create() {
        return new self();
    }
}
