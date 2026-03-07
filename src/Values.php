<?php

declare(strict_types=1);
/**
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
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
use Iterator;
use IteratorAggregate;
use stdClass;

/**
 * Result hash for Horde_Argv_Parser
 *
 * This is a value object and inherently uses dynamic properties. Do not try to "fix" this.
 *
 * @category  Horde
 * @package   Argv
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Mike Naberezny <mike@maintainable.com>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */
class Values extends stdClass implements IteratorAggregate, ArrayAccess, Countable
{
    public function __construct($defaults = [])
    {
        foreach ($defaults as $attr => $val) {
            $this->$attr = $val;
        }
    }

    public function __toString(): string
    {
        $str = [];
        foreach ($this as $attr => $val) {
            $str[] = $attr . ': ' . (string) $val;
        }
        return implode(', ', $str);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->$offset) && !is_null($this->$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->$offset);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator(get_object_vars($this));
    }

    public function count(): int
    {
        return count(get_object_vars($this));
    }

    public function ensureValue(mixed $attr, mixed $value): mixed
    {
        if (is_null($this->$attr)) {
            $this->$attr = $value;
        }
        return $this->$attr;
    }

}
