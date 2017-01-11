<?php

namespace Prelude\Utility;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use RuntimeException;

final class DynObject implements ArrayAccess, Countable {
    private $propMap;
    private $readOnly;
    private $extensible;
    
    const OPTION_MODE = 'mode';
    
    const MODE_DEFAULT =  'default';
    const MODE_READ_ONLY = 'readOnly';
    const MODE_EXTENSIBLE = 'extensible';

    private function __construct(array $props, array $options = null) {
        $this->propMap = [];
        
        foreach ($props as $k => $v) {
            $this->propMap[$k] = $v;
        }
        
        $mode = @$options[self::OPTION_MODE];
        
        if ($mode === null || $mode === self::MODE_DEFAULT) {
            $this->readOnly = false;
            $this->extensible = false;
        } else if ($mode === self::MODE_READ_ONLY) {
            $this->readOnly = true;
            $this->extensible = false;
        } else if ($mode === self::MODE_EXTENSIBLE) {
            $this->readOnly = false;
            $this->extensible = true;
        } else {
            throw new InvalidArgumentException(
                "[DynObject::__construct] Illegal value for option 'mode'");
        }
    }

    public function __get($propName) {
        if (!is_string($propName)) {
            throw new InvalidArgumentException(
                '[DynObject#__get] First argument $propName must be a string');
        } else if (!array_key_exists($propName, $this->propMap)) {
            throw new InvalidArgumentException(
                "[DynObject#__get] Tried to read unknown property '$propName'");
        }

        return $this->propMap[$propName];
    }

    public function __set($propName, $value) {
        if (!is_string($propName)) {
            throw new InvalidArgumentException(
                '[DynObject#__set] First argument $propName must be a string');
        }
        
        if ($this->readOnly) {
            throw new RuntimeException(
                'The dynamic object is read-only - '
                . "cannot set value for property '$propName'");
        } else if ( !$this->extensible
            && !array_key_exists($propName, $this->propMap)) {
            
            throw new RuntimeException(
                'The dynamic object is not extensible - a value for property '
                . "name '$propName' cannot be set");
        }
        
        $this->propMap[$propName] = $value;
    }

    function toArray() {
        return $this->propMap();
    }

    public function offsetExists($propName) {
        return array_key_exists($this->propMap, $propName);
    }

    public function offsetGet($propName) {
        $ret = $this->__get($propName);
        return $ret;
    }

    public function offsetSet($propName, $value) {
        $this->__set($propName, $value);
    }

    public function offsetUnset($propName) {
        unset($this->propMap[$propName]);
    }

    public function count() {
        $ret = count($this->propMap);
        return $ret;
    }
    
    static function from($props, array $options = null) {
        return new self($props, $options);
    }
}
