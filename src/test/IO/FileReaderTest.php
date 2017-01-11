<?php

namespace Prelude\IO;

require_once __DIR__ . '/../../../include.php';

class FileReaderTest extends \PHPUnit_Framework_TestCase {
    function testMethodReadFully() {
        $filename = tempnam(sys_get_temp_dir(), 'txt');
        
        file_put_contents($filename, "a\nb\nc");
        
        $content =
            FileReader::fromFile($filename)
                ->readFully();
        
        $this->assertEquals($content, "a\nb\nc");
        
/*
        $content =
            FileReader::fromString('this is a test')
                ->readFully();
                
        $this->assertEquals($content, 'this is a test');
*/
    }
    
    function testMethodReadLines() {
        return;
        $filename = tempnam(sys_get_temp_dir(), 'txt');
        
        file_put_contents($filename, "a\r\nb\r\nc");
        
        $lines = FileReader::fromFile($filename)
            ->readSeq()
            ->toArray();

        $this->assertEquals($lines, ['a', 'b', 'c']);
    }
}
