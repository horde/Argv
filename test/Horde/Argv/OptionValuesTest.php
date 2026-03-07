<?php

namespace Horde\Argv;

use Horde_Argv_Values;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class OptionValuesTest extends TestCase
{
    public function testBasics()
    {
        $values = new Horde_Argv_Values();
        $this->assertEquals([], iterator_to_array($values));
        $this->assertNotEquals(['foo' => 'bar'], $values);
        $this->assertEquals('', (string) $values);

        $dict = ['foo' => 'bar', 'baz' => 42];
        $values = new Horde_Argv_Values($dict);
        $this->assertEquals($dict, iterator_to_array($values));
        $this->assertNotEquals(['foo' => 'bar'], $values);
        $this->assertNotEquals([], $values);
        $this->assertNotEquals('', (string) $values);
    }
}
