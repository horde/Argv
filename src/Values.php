<?php
declare(strict_types=1);
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
namespace Horde\Argv;
use ArrayIterator;
use ArrayAccess;
use Countable;
use IteratorAggregate;

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
class Values implements IteratorAggregate, ArrayAccess, Countable
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

    public function offsetExists($attr)
    {
        return isset($this->$attr) && !is_null($this->$attr);
    }

    public function offsetGet($attr)
    {
        return $this->$attr;
    }

    public function offsetSet($attr, $val)
    {
        $this->$attr = $val;
    }

    public function offsetUnset($attr)
    {
        unset($this->$attr);
    }

    public function getIterator()
    {
        return new ArrayIterator(get_object_vars($this));
    }

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
