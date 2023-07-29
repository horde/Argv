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
#[\AllowDynamicProperties]
class Horde_Argv_Values implements IteratorAggregate, ArrayAccess, Countable
{
    public function __construct($defaults = array())
    {
        foreach ($defaults as $attr => $val) {
            $this->$attr = $val;
        }
    }

    public function __toString()
    {
        $str = array();
        foreach ($this as $attr => $val) {
            $str[] = $attr . ': ' . (string)$val;
        }
        return implode(', ', $str);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($attr)
    {
        return isset($this->$attr) && !is_null($this->$attr);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($attr)
    {
        return $this->$attr;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($attr, $val)
    {
        $this->$attr = $val;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($attr)
    {
        unset($this->$attr);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator(get_object_vars($this));
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return count(get_object_vars($this));
    }

    public function ensureValue($attr, $value)
    {
        if (is_null($this->$attr)) {
            $this->$attr = $value;
        }
        return $this->$attr;
    }

}
