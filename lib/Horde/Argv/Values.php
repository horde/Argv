<?php
/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * This package is ported from Python's Optik (http://optik.sourceforge.net/).
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Mike Naberezny <mike@maintainable.com>
 * @license  http://www.horde.org/licenses/bsd BSD
 * @category Horde
 * @package  Argv
 */

/**
 * Result hash for Horde_Argv_Parser
 *
 * @category  Horde
 * @package   Argv
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Mike Naberezny <mike@maintainable.com>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */

class Horde_Argv_Values implements IteratorAggregate, ArrayAccess, Countable
{
    // Array to store the dynamic attributes
    private $data = [];

    /**
     * Summary of __construct
     * @param mixed $defaults
     */
    public function __construct($defaults = [])
    {
        foreach ($defaults as $attr => $val) {
            $this->data[$attr] = $val;
        }
    }

    /**
     * __set: Set a value
     * 
     * @param string $attr The name of the attribute
     * @param mixed $value The content of the attribute
     */
    public function __set($attr, $value): void
    {
        $this->data[$attr] = $value;
    }

    /**
     * __get: Returns a value of an attribute
     * 
     * @param string $attr The name of the attribute
     * @return mixed       The value of the attribute
     */
    public function &__get($attr)
    {
        return $this->data[$attr];
    }

    /**
     * __isset: check if an attribute is set
     * 
     * @param string $attr The name of the attribute
     * @return bool        True, when the attribute exists/ is set else false
     */
    public function __isset($attr): bool
    {
        return isset($this->data[$attr]);
    }
    
    /**
     * __unset: removes a attribute
     * 
     * @param string $attr The name of the attribute
     * @return void
     */
    public function __unset($attr): void
    {
        unset($this->data[$attr]);
    }
    
    /**
     * __toString: The whole content as a string
     * 
     * @return string The content as a string
     */
    public function __toString(): string
    {
        $str = [];
        foreach ($this->data as $attr => $val) {
            $str[] = $attr . ': ' . (string)$val;
        }
        return implode(', ', $str);
    }

    /**
     * Summary of offsetExists
     * @param mixed $attr
     * @return bool
     */
    public function offsetExists($attr): bool
    {
        return isset($this->data[$attr]) && !$this->data[$attr] === null;
    }

    /**
     * Summary of offsetGet
     * @param mixed $attr
     * @return mixed
     */
    public function offsetGet($attr)
    {
        return $this->data[$attr] ?? null;
    }

    /**
     * Summary of offsetSet
     * @param mixed $attr
     * @param mixed $val
     * @return void
     */
    public function offsetSet($attr, $val): void
    {
        $this->data[$attr] = $val;
    }

    /**
     * Summary of offsetUnset
     * @param mixed $attr
     * @return void
     */
    public function offsetUnset($attr): void
    {
        unset($this->data[$attr]);
    }

    /**
     * Summary of getIterator
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Summary of count
     * @return int
     */
    public function count(): int
    {
        return count(get_object_vars($this->data));
    }

    /**
     * Summary of ensureValue
     * @param mixed $attr
     * @param mixed $value
     * @return mixed
     */
    public function ensureValue($attr, $value)
    {
        if ($this->data[$attr] === null) {
            $this->data[$attr] = $value;
        }
        return $this->data[$attr];
    }

}
