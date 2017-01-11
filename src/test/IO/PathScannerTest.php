<?php

namespace Prelude\IO;

require_once __DIR__ . '/../../../include.php';

use PHPUnit_Framework_TestCase;

class PathScannerTest extends PHPUnit_Framework_TestCase {
    function testMethodScan() {
/*
        $arr =
            PathScanner::create()
                ->recursive()
                ->includeFiles(['*.php', '*.json'])
                ->excludeFiles('*tmp*')
                ->excludeLinks()
                //->forceAbsolute()
                ->sort(FileComparators::byFileSize())
                ->listPaths()
                ->scan('.')
                ->map(function ($file) {
                    return $file . " :: " . filesize($file);
                })
                ->toArray();

        print_r($arr);
*/        
        // Determine the number of-non blank PHP lines in the "src" folder:
        // "scan" returns a lazy sequence of files, "flatMap" takes
        // that file sequence, maps each file entry to a sequence of text
        // lines and flattens the seqence of text line sequences afterwards
        // into a single senquence of text lines.
        // All of that happens completely lazily.
        $lineCount =
            PathScanner::create()
                ->recursive()
                ->includeFiles('*.php')
                ->forceAbsolute()
                ->scan('./src/main')
                ->flatMap(function ($file) {
                    return FileReader::fromFile($file)
                        ->readSeq();
                })
                ->filter(function ($line, $idx) {
                    return trim($line) !== '';
                })
                ->count();
                
        print "Number of non-blank PHP lines in directory 'src/main': $lineCount\n";
    }
}
