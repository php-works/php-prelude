<?php

namespace Prelude\CSV;

use InvalidArgumentException;
use Prelude\IO\FileWriter;
use Prelude\Utility\CharsetRecoder;
use Prelude\Utility\Seq;

final class CSVExporter {
    private $format;
    private $mapper;
    private $charsetRecoder;

    private function __construct() {
        $this->format = CSVFormat::create();
        $this->mapper = null;
        $this->charsetRecoder = null;
    }
    
    function format(CSVFormat $format) {
        $ret = $this;
        
        if ($format !== $this->format) {
            $ret = clone $this;
            $ret->format = $format;
        }

        return $ret;
    }
    
    function mapper($mapper) {
        $ret = $this;
        
        if ($mapper !== $this->mapper) {
            $ret = clone $this;
            $ret->mapper = $mapper;
        }
        
        return $ret;
    }
    
    function charsetRecoder($charsetRecoder) {
        $ret = $this;
        
        if ($charsetRecoder !== $this->charsetRecoder) {
            $ret = clone $this;
            $ret->charsetRecoder = $charsetRecoder;
        }
        
        return $ret;
    }

    function export(Seq $recs, FileWriter $writer) {
        $params = $this->format->getParams();
        $columns = $params['columns'];
        $delimiter = $params['delimiter'];
        $quoteChar = $params['quoteChar']; 
        $escapeChar = $params['escapeChar']; 
        $suppressHeader = $params['suppressHeader'];
        $autoTrim = $params['autoTrim'];
        $stream = $writer->open();
        
        $recs = $this->applyMapper($recs);
        
        $columnNameToIndexMap =
            $columns !== null
                ? array_flip(array_values($columns))
                : [];
        try {
            if ($columns !== null && !$suppressHeader) {
                fputcsv(
                    $stream,
                    $columns,
                    $delimiter,
                    $quoteChar,
                    $escapeChar);
            }
            
            foreach ($recs as $item) {
                if (!($item instanceof Seq)) {
                    $item = Seq::of($item);
                }
                
                foreach ($item as $rec) {
                    $columnCount =
                        $columns !== null
                        ? count($columns)
                        : count($rec);
                    
                    $newMap = array_fill(0, $columnCount, null);
                    $idx = -1;
    
                    foreach ($rec as $key => $value) {
                        ++$idx;
                        
                        if ($value !== null && !is_scalar($value)) {
                            $value = null; // TODO - throw an exception
                        } else if ($autoTrim) {
                            $value = trim($value);
                        }
                       
                        if ($this->charsetRecoder !== null) {
                            $value =
                                $this->charsetRecoder->recodeString($value);
                        } 
                        
                        if ($columns === null) {
                            $newMap[$idx] = $value;
                        } else {
                            if (is_numeric($key)) {
                                if ($key < count($newMap)) {
                                    $newMap[(int)$key] = $value;
                                }
                            } else {
                                $targetIdx = @$columnNameToIndexMap[$key];
                                
                                if ($targetIdx !== null) {
                                    $newMap[$targetIdx] = $value;
                                }
                            }
                        }
                    }
                    
                    fputcsv(
                        $stream,
                        $newMap,
                        $delimiter,
                        $quoteChar,
                        $escapeChar);
                        
                    fflush($stream);
                }
            }
        } finally {
            fclose($stream);
        }
    }   

    static function create() {
        return new self();
    }
    
    private function applyMapper(Seq $recs) {
        $ret = $recs;
        
        if ($this->mapper !== null) {
            $ret =  
                $recs
                    ->map(function ($rec, $idx) {
                        $mapper = $this->mapper;
                        return $mapper($rec, $idx);
                    });
        }
        
        $ret =
            $ret
                ->filter(function ($item) {
                    return is_array($item) || $item instanceof Seq;
                });

        return $ret;
    }
}
