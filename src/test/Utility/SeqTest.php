<?php

namespace Prelude\Utility;

require_once __DIR__ . '/../../../include.php';

error_reporting(E_ALL);

use PHPUnit_Framework_TestCase;

class SeqTest extends PHPUnit_Framework_TestCase {
    function testMethodFrom() {
        $this->assertEquals(
            Seq::from([1, 2, 3])
                ->toArray(),
            [1, 2, 3]);
        
        $this->assertEquals(
            Seq::from(function () {
                    yield 2;
                    yield 4;
                    yield 6;
                })
                ->toArray(),
            [2, 4, 6]);
    }
    
    function testMethodFilter() {
        $arr = Seq::range(1, 10)
            ->filter(function ($n) {
                return $n % 2 == 0;
            })
            ->toArray();

        $this->assertEquals($arr, [2, 4, 6, 8]);
    }
    
    function testMethodMap() {
        $arr = Seq::range(1, 4)
            ->map(function ($n) {
                return $n * 2;
            })
            ->toArray();
            
        $this->assertEquals($arr, [2, 4, 6]);
    }
    
    function testMethodTake() {
        $arr = Seq::range(1, 100)
            ->take(4)
            ->toArray();
            
        $this->assertEquals($arr, [1, 2, 3, 4]);
    }
    
    function testMethodTakeWhile() {
        $arr = Seq::range(0, 10)->takeWhile(function ($n) {
            return $n < 5;
        })
        ->toArray();
        
        $this->assertEquals($arr, [0, 1, 2, 3, 4]);
    }
    
    function testMethodSkip() {
        $arr = Seq::range(1, 6)
            ->skip(3)
            ->toArray();
        
        $this->assertEquals($arr, [4, 5]);
    }
    
    function testMethodSkipWhile() {
        $arr = Seq::range(0, 10)->skipWhile(function ($n) {
            return $n < 5;
        })
        ->toArray();
        
        $this->assertEquals($arr, [5, 6, 7, 8, 9]);
    }
    
    function testMethodFlatten() {
        $seqOfSeqs = Seq::of(
            Seq::of(1, 2), Seq::of(3, 4), Seq::of(5));
        
        $flattenedSeq = $seqOfSeqs->flatten();
        
        $this->assertEquals($flattenedSeq->toArray(),
            [1, 2, 3, 4, 5]);
    }
   
    function testMethodReduce() {
        $multiply = function ($a, $b) {
            return $a * $b;
        };
        
        $value1 = Seq::range(1, 4)->reduce($multiply, null);
        $this->assertEquals($value1, 0);
        
        $value2 = Seq::range(1, 4)->reduce($multiply, 1);
        $this->assertEquals($value2, 6);
    }
    
    function testMethodCount() {
        $count = Seq::range(1, 100)
            ->count();
            
        $this->assertEquals($count, 99);
    }
    
    function testMethodEach() {
        $arr = [];
        
        Seq::range(1, 4)
            ->each(function ($item) use (&$arr) {
                array_push($arr, $item);
            });
            
        $this->assertEquals($arr, [1, 2, 3]);
    }

    function testMethodFrom2() {
        $seq1 = Seq::from([1, 2, 3]);
        $seq2 = Seq::from($seq1);
        $this->assertEquals($seq1, $seq2);
        
        $arr1 = Seq::from([2, 4, 6])->toArray();
        $this->assertEquals($arr1, [2, 4, 6]);
        
        $arr2 = Seq::from("dummy text")->toArray();
        $this->assertEquals($arr2, []);
    }

    function testMethodRange() {
        $arr1 = Seq::range(1, 5)->toArray();
        $this->assertEquals($arr1, [1, 2, 3, 4]);

        $arr2 = Seq::range(4, 0)->toArray();
        $this->assertEquals($arr2, []);

        $arr3 = Seq::range(4, 0, -1)->toArray();
        $this->assertEquals($arr3, [4, 3, 2, 1]);
    }
    
    function testMethodIterate() {
        $arr =
            Seq::iterate([0, 1], function($a, $b) {
                return $a + $b;
            })
            ->take(10)
            ->toArray();
        
        $this->assertEquals($arr, [0, 1, 1, 2, 3, 5, 8, 13, 21, 34]);
    }
}
