<?php
/**
 * php-prelude
 * 
 * @link       https://github.com/mcjazzyfunky/php-prelude
 * @copyright  Copyright (c) 2015-2017
 * @license    New BSD License
 */

namespace Prelude\Utility;

use Closure;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use UnexpectedValueException; 

/**
 * Stateless representation of a lazy sequence that can either enumerate
 * a finite or an infite number of items.
 * Class Seq extends the PHP standard interface IteratorAggregate therefore
 * sequences can be used in foreach loops (nevertheless, Seqs also provides
 * a higher order method called 'each' which does the same in a more functional
 * fashion).
 * As Seqs are stateless and immutable, they can be traversed as often as
 * desired and also passed around without fearing any unexpected side effects.
 * 
 * Class Seq is comparable to similar-well known counterparts
 * in other programming languages:
 * 
 * - "Stream" in Java 8, Scala and Scheme
 * - "Seq" in Clojure/ClojureScript
 * - "Lazy list" in Haskell
 * - "IEnumerable" in C#
 * - etc.
 * 
 * Several factory methods are available to build new sequences
 * (e.g., all of the following examples generate the same sequence:
 * 1, 2, 3, 4):
 * 
 * ``` 
 * - Seq::range(1, 5)
 * - Seq::from([1, 2, 3, 4])
 * - Seq::from(function () {
 *       yield 1;
 *       yield 2;
 *       yield 3;
 *       yield 4;
 *   } 
 * - etc.
 * ```
 */
final class Seq implements IteratorAggregate {
    /**
     * Generator function that yields the items of the Seq one by one
     * 
     * var Closure 
     */ 
    private $generatorFunction;
    
    /**
     * Arguments for the generator function that will be invoked at the
     * beginning of the sequence iteration
     * 
     * var array|null
     */
    private $args;
   
    /**
     * Constructor
     * 
     * @param Closure $generatorFunction
     *     Yields the items of the Seq
     * @param array|null $args
     *     Arguments when applying the generator function at the beginning
     *     of the iteration.
     */
    private function __construct(
        callable $generatorFunction,
        array $args = null) {
        
        $this->generatorFunction = $generatorFunction;
        $this->args = $args;
    }
   
    /**
     * Filters the sequence by a filter predicate
     * 
     * Example:
     *    ```
     *    Seq::range(1, 10)
     *        ->filter(function ($n) {
     *            return $n % 2 === 0;
     *        })
     *    // Result: 2, 4, 6, 8
     *    ```
     * 
     * @param callable $pred
     *   The predicate function used for filtering
     *   First argument will be the sequence item.
     *   Second argument will be the current iteration index
     *   starting with 0.
     * @return Seq The filtered sequence
     */
    function filter(callable $pred) {
        return new self(function () use ($pred) {
            $idx = -1;
            
            foreach ($this as $item) {
                if ($pred($item, ++$idx)) {
                    yield $item;
                }
            }
        });
    }
    
    /**
     * Filters the sequence by a rejecting items that satisfy a given predicate 
     *
     * Example:
     *   ```
     *   Seq::from([1, 2, 3, 4, 5, 6, 7])
     *       ->reject(function ($n) {
     *           return $n % 3 === 0;
     *       });
     *   // Result: 1, 2, 4, 5, 7
     *   ```
     * 
     * @param callable $pred
     *   The predicate function used for filtering to pick up the items that
     *   shall be rejected.
     *   First argument will be the sequence item.
     *   Second argument will be the current iteration index
     *   starting with 0.
     * @return Seq
     *   The filtered sequence
     */
    function reject(callable $pred) {
        return $this->filter(function ($item, $idx) {
            return !$pred($item, $idx);
        });
    }
   
    /**
     * Filters the sequence by rejecting all null values.
     *
     * Example:
     *   ```
     *   Seq::from([1, null, 2, null, null, 3])
     *       ->rejectNulls()
     *   // Result: 1, 2, 3
     *   ```
     * 
     * @return Seq
     *   Null-value free sequence
     */ 
    function rejectNulls() {
        return $this->filter(function ($item) {
            return $item !== null;
        });
    }

    /**
     * Transforms the sequence to another sequence of the same length
     * by mapping each element using a given mapper function
     *
     * Example:
     *   ```
     *   Seq::map([1, 2, 3])
     *       ->map(function ($n) {
     *           return $n * $n;
     *       });
     *   // Result: 1, 4, 9
     *   ```
     * 
     * @param callable $fn
     *   The mapper function
     * @return Seq
     *   The transformed Seq
     */ 
    function map(callable $fn) {
        return new self(function () use ($fn) {
            $idx = -1;
            
            foreach ($this as $item) {
                yield $fn($item, ++$idx);
            }
        });
    }
   
    /**
     * Limits the sequence of to the first nth items.
     * 
     * Example:
     *   ```
     *   Seq::range(2, 10)
     *       ->take(4)
     *   // Result: 2, 3, 4, 5
     *   ```
     * @param int $n
     *   The maximal length of the limited sequence
     * @return Seq
     *   The limited sequence
     */
    function take($n) {
        if (!is_int($n)) {
            throw new InvalidArgumentException(
                '[Seq#take] First argument $n must be an integer');
        }
        
        return new self(function () use ($n) {
            $idx = -1;
            
            foreach ($this as $item) {
                if (++$idx < $n) {
                    yield $item;
                } else {
                    break;
                }
            }
        });
    }
   
    /**
     * Limits the sequence by taking as many items as satisfying the
     * given predicate
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 4, 8, 16, 32])
     *       ->takeWhile(function ($n) {
     *           return $n < 10;
     *       })
     *   // Result: 1, 2, 4, 8
     * 
     * @param callable $pred
     *   The predicate to test the items
     * @result Seq
     *   The limited sequence
     */
    function takeWhile(callable $pred) {
        return new self(function () use ($pred) {
            $idx = -1;
            
            foreach ($this as $item) {
                if (!$pred($item, ++$idx)) {
                    break;
                }
                
                yield $item;
            }
        });
    }
   
    /**
     * Omits the first n items of the sequence
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 4, 8, 16, 32])
     *       ->skip(3)
     *   // Result: 8, 16, 32
     *   ```
     *
     * @param int $n
     *   The number of items to be ignored
     * @result Seq
     *   The resulting subsequence
     */
    function skip($n) {
        if (!is_int($n)) {
            throw new InvalidArgumentException(
                '[Seq#skip] First argument $n must be an integer');
        }
        
        return new self(function () use ($n) {
            $idx = -1;
            
            foreach ($this as $item) {
                if (++$idx >= $n) {
                    yield $item;
                }
            }
        });
    }

    /**
     * Omits leading elements of the sequence as long as they satisfy a
     * given predicate
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 4, 8, 16, 32])
     *       ->skipWhile(function ($n) {
     *           return $n < 10;
     *       });
     *   // Result: 16, 32
     *   ```
     * 
     * @param callable $pred
     *   The predicate to test the items
     * @return Seq
     *   The resulting subsequence
     */
    function skipWhile(callable $pred) {
        return new self(function () use ($pred) {
            $idx = -1;
            $started = false;
            
            foreach ($this as $item) {
                if (!$started && !$pred($item, ++$idx)) {
                    $started = true;
                }
                
                if ($started) {
                    yield $item;
                }
            }
        });
    }
    
    /**
     * Maps each element of the sequence to a sequence (using Seq::from)
     * and flattens the sequence of sequences to a single sequence
     * afterwards
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 3], Seq::from([4, 5]))
     *       ->flatten()
     *   // Result: 1, 2, 3, 4, 5
     *   ```
     *
     * @result Seq
     *   The flattened sequence
     */
    function flatten() {
        return new self(function () {
            foreach ($this as $item) {
                foreach (Seq::from($item) as $subitem) {
                    yield $subitem;
                }
            } 
        });
    }
   
    /**
     * Maps each element of a seqence by the given mapper function,
     * interpretes each mapped value as sequence and flattens the
     * sequence of seqences afterwards
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 3])
     *       ->flatMap(function ($n) {
     *           return Seq::range($n, 4);
     *       })
     *   // Result: 1, 2, 3, 2, 3, 3
     *   ```
     * 
     * @param callable $fn
     *     The mapping function
     * @return Seq
     *     The resulting seuence after the mapping and flattening
     */
    function flatMap(callable $fn) {
        return $this->map($fn)->flatten();
    }
    
    /**
     * Prepends a single element at the front of the sequence
     * 
     * Example:
     *   ```
     *   Seq::from([2, 3, 4])
     *       ->prepend(1)
     *   // Result: 1, 2, 3, 4
     * 
     * @param mixed $item
     *   The item to be prepended at the front
     * @return Seq
     *   The resulting sequence
     */
    function prepend($item) {
        return Seq::concat(Seq::of($item), $this);
    }
    
    /**
     * Prepends multiple elements at the front of the sequence
     * 
     * Example:
     *   ```
     *   Seq::from([3, 4, 5])
     *       ->prependMany([1, 2])
     *   // Result: 1, 2, 3, 4, 5
     *   ```
     * 
     * @param array $items
     *    The items to be prepended at the front
     * @return
     *   The resulting sequence
     */
    function prependMany($items) {
        return Seq::concat($items, $this);
    }

    /**
     * Appends a single element at the end of the sequence
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 3])
     *       ->append(4)
     *   // Result: 1, 2, 3, 4
     * 
     * @param mixed $item
     *   The item to be appended at the end
     * @return Seq
     *   The resulting sequence
     */
    function append($item) {
        return Seq::concat($this, Seq::of($item));
    }
    
    /**
     * Appends multiple elements at the end of the sequence
     * 
     * Example:
     *   ```
     *   Seq::from([1, 2, 3])
     *       ->appendMany([4, 5])
     *   // Result: 1, 2, 3, 4, 5
     *   ```
     * 
     * @param array $items
     *    The items to be appended at the front
     * @return
     *   The resulting sequence
     */
    function appendMany($items) {
        return Seq::concat($this, $items);
    }
    
    /**
     * Sorts a sequence
     * 
     * Example:
     *   ```
     *   Seq::from([2, 4, 3, 1])
     *       ->sort()
     *   // Result: 1, 2, 3, 4
     *   ```
     * 
     * @param mixed $order
     *    The sort order, must either be an integer constant as used for
     *    PHP standard function 'sort' or a callable to provide a customized
     *    sort function
     * @return Seq
     *    The sorted sequence
     */
    function sort($order = SORT_REGULAR) {
        if (!is_integer($order) && !is_callable($order)) {
            throw new InvalidArgumentException(
                '[Seq#sort] First argument $order must either be an integer '
                . 'or a callable');
        }
        
        return new self(function () use ($order) {
            $arr = $this->toArray();
            
            if ($order === null) {
                sort($arr);
            } else if (is_integer($order)) {
                sort($arr, $order);
            } else {
                usort($arr, $order);
            }
            
            foreach ($arr as $item) {
                yield $item;
            }
        });
    }
    
    function peek(callable $action) {
        return new self(function () use ($action) {
            $idx = -1; 
            
            foreach ($this as $item) {
                $action($this, ++$idx);
                yield $item;
            }
        });
    }
    
    function reduce(callable $fn, $initialValue = null) {
        $idx = -1;
        $lastValue = $initialValue;
        
        foreach ($this as $item) {
            $lastValue = $fn($lastValue, $item, ++$idx);
        }
        
        if ($idx === -1) {
            $ret = $initialValue;
        } else {
            $ret = $lastValue;
        }
        
        return $ret;
    }
    
    function max(callable $comparator = null, $defaultValue = null) {
        $ret = $defaultValue;
        $isFirst = true;
        
        foreach ($this as $item) {
            if ($isFirst) {
                $ret = $item;
                $isFirst = false;
            } else {
                if ($comparator === null) {
                    if ($item > $ret) {
                        $ret = $item;
                    }
                } else {
                    $result = $comparator($item, $ret);
                    
                    if ($result >= 1) {
                        $ret = $item;
                    }
                }             
            }
        }
        
        return $ret;
    }

    function min(callable $comparator = null, $defaultValue = null) {
        $ret = $defaultValue;
        $isFirst = true;
        
        foreach ($this as $item) {
            if ($isFirst) {
                $ret = $item;
                $isFirst = false;
            } else {
                if ($comparator === null) {
                    if ($item < $ret) {
                        $ret = $item;
                    }
                } else {
                    $result = $comparator($ret, $item);
                    
                    if ($result >= 1) {
                        $ret = $item;
                    }
                }             
            }
        }

        return $ret;        
    }
    
    function count() {
        $count = 0;
        
        foreach ($this as $item) {
            ++$count;
        }
        
        return $count;
    }
    
    function each(callable $fn) {
        if (!is_callable($fn)) {
            throw new InvalidArgumentException(
                '[Seq.each] First argument $fn must be a function');
        }
       
        $idx = -1;
        
        foreach ($this as $item) {
            ++$idx;
            $fn($item, $idx);
        }
        
        return $idx;
    }
    
    function toArray() {
        $ret = [];
        
        foreach ($this as $item) {
            array_push($ret, $item);
        }
        
        return $ret;
    }
    
    function force() {
        return Seq::from($this->toArray());
    }
    
    function getIterator() {
        $generatorFunction = $this->generatorFunction;
        
        if ($this->args === null) {
            $ret = $generatorFunction();
        } else {
            $ret = call_user_func_array($generatorFunction, $this->args);
        }
        
        if (!($ret instanceof Generator)) {
            throw new UnexpectedValueException(
                '[Seq#getIterator] Generator function did not really return a generator');
        }
        
        return $ret;
    }
    
    static function of(/*...$items*/) {
        $items = func_get_args();

        return new self(function () use ($items) {
            foreach ($items as $item) {
                yield $item;
            } 
        });
    }
    
    static function create(Closure $generatorFunction, array $args = null) {
        return new self($generatorFunction, $args);
    }
    
    static function from($source) {
        $ret = null;
        
        if ($source instanceof Seq) {
            $ret = $source;
        } else if (is_array($source) || $source instanceof IteratorAggregate) {
            $ret = new self(function () use ($source) {
                foreach ($source as $item) {
                    yield $item;
                } 
            });
        } else if ($source instanceof Closure) {
            $ret = new self($source);
        } else {
            $ret = self::nil();
        }
        
        return $ret;
    }
    
    static function isIterable($source) {
        return (is_array($source) || $source instanceof IteratorAggregate);
    }
    
    static function nil() {
        return Seq::from([]); 
    }
    
    static function range($start, $end, $step = 1) {
        if (!is_int($start)) {
            throw new InvalidArgumentException(
                '[Seq.range] First argument $start must be an integer');
        } else if (!is_int($end)) {
            throw new InvalidArgumentException(
                '[Seq.range] Second argument $end must be an integer');
        } else if (!is_int($step) || $step === 0) {
            throw new InvalidArgumentException(
                '[Seq.range] Thrid argument $step must be an non-zero integer');
        }
        
        return new self(function () use ($start, $end, $step) {
            if ($start < $end && $step > 0) {
                for ($i = $start; $i < $end; $i += $step) {
                    yield $i;
                } 
            } else if ($start > $end && $step < 0) {
                for ($i = $start; $i > $end; $i += $step) {
                    yield $i;
                } 
            }
        });
    }
    
    static function iterate(array $startValues, callable $fn) {
        return new self(function () use ($startValues, $fn) {
            foreach ($startValues as $value) {
                yield $value;
            }
            
            $values = $startValues;

            while (true) {
                $value = call_user_func_array($fn, $values);
                array_push($values, $value);
                array_shift($values);

                yield $value;
            }
        });
    }
    
    static function repeat($item, $count = null) {
        return new self(function () use ($item, $count) {
            $idx = -1;
            
            while ($count === null || ++$idx < $count) {
                yield $item;
            }
        });
    }
    
    static function cycle($items, $count = null) {
        $seq = Seq::from($items);

        return new self(function () use ($seq, $count) {
            $idx = -1;
            
            while ($count === null || ++$idx < $count) {
                foreach ($seq as $item) {
                    yield $item;
                }
            }
        });
    }
    
    static function concat($iterable1, $iterable2) {
        return self::concatMany([$iterable1, $iterable2]);
    }
     
    static function concatMany($iterable) {
        $seq = Seq::from($iterable);
        
        return new self(function () use ($seq) {
            foreach ($seq as $items) {
                foreach(Seq::from($items) as $item) {
                    yield $item;
                }
            } 
        });
    }

    static function zip($iterable1, $iterable2, callable $fn = null) {
        $seq1 = Seq::from($iterable1);
        $seq2 = Seq::from($iterable2);
        
        return new self(function () use ($seq1, $seq2, $fn) {
            $generator1 = null;
            $generator2 = null;

            try {
                $generator1 = $seq1->getIterator();
                $generator2 = $seq2->getIterator();
                
                while ($generator1->valid() && $generator2->valid()) {
                    $item1 = $generator1->current();
                    $item2 = $generator2->current();
                    
                    $generator1->next();
                    $generator2->next();
                    
                    if ($fn === null) {
                        yield [$item1, $item2];
                    } else {
                        yield $fn($item1, $item2);
                    }
                }
            } finally {
                $generator1 = null;
                $generator2 = null;
            }
        });
    }

    static function zipMany($iterable, callable $fn = null) {
        $iterables =
            is_array($iterable)
            ? $iterable
            : Seq::from($iterable)->toArray();
        
        return new self(function () use ($iterables, $fn) {
            $iterators = [];
            
            try {
                foreach ($iterables as $iterable) {
                    $iterators[] = Seq::from($iterable)->getIterator();
                }
                
                $idx = -1;
                
                while (true) {
                    foreach ($iterators as $iterator) {
                        if (!$iterator->valid()) {
                             break(2);
                        }
                    }

                    $items = [];                    
                    
                    foreach ($iterators as $iterator) {
                        $items[] = $iterator->current();
                        $iterator->next();
                    }                
                    
                    if ($fn === null) {
                        yield $items;
                    } else {
                        yield call_user_func_array($fn, $items);
                    }
                }
            } finally {
                $iterators = null;
            }
        });
    }
}
