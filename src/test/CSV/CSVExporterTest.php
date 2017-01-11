<?php

namespace Prelude\CSV;

require_once __DIR__ . '/../../../include.php';

use Exception;
use PHPUnit_Framework_TestCase;
use Prelude\Utility\CharsetRecoder;
use Prelude\Utility\Seq;
use Prelude\IO\FileWriter;

class CSVExporterTest extends PHPUnit_Framework_TestCase {
    function testRun() {
        
        // Please be aware that the following recordsets vary
        // structurally
        $recs = [
            ['LAST_NAME' => 'Iverson',
             'FIRST_NAME' => 'Allen',
             'CITY' => 'Hampton',
             'COUNTRY' => 'USA'],
            ['FIRST_NAME' => 'Dirk',
             'LAST_NAME' => 'Nowitzki',
             'CITY' => 'Wuerzburg',
             'COUNTRY' => 'Germany'],
            ['Michael "Air"', 'Jordan', 'New York', 'USA', 'This field will not be exported']
        ];
        
        $format =
            CSVFormat::create()
                ->columns(['FIRST_NAME', 'LAST_NAME', 'CITY', 'COUNTRY'])
                ->suppressHeader(false)
                ->delimiter(';')
                ->quoteChar('"');

        $exporter =
            CSVExporter::create()
                ->format($format)
                ->mapper(function ($rec, $idx) {
                    // Add some twins in Vienna - just for a test ;-)
                    $rec2 = $rec;
                    $rec2['LAST_NAME'] = 'Doppelganger';
                    $rec2['CITY'] = 'Vienna';
                    $rec2['COUNTRY'] = 'Austria';
                    
                    return Seq::from([$rec, $rec2]);
                })
                ->charsetRecoder(
                    CharsetRecoder::create('UTF-8', 'ISO-8859-1'))
                ->export(
                    Seq::from($recs),
                    FileWriter::fromFile('php://stdout'));
    }
}





