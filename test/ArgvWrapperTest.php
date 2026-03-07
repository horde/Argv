<?php

/**
 * @author     Ralf Lang <ralf.lang@ralf-lang.de>
 */

namespace Horde\Argv\Test;

use PHPUnit\Framework\TestCase;
use Horde\Argv\ArgvWrapper;

/**
 * @coversNothing
 */
class ArgvWrapperTest extends TestCase
{
    public function testCountArguments()
    {
        $argv = new ArgvWrapper(['foo', 'bar']);
        $this->assertEquals(count($argv), 2);
        $this->assertEquals($argv->count(), 2);
        $this->assertCount(2, $argv, 'Object wrapping two values counts two.');
    }

    public function testCastingToArray()
    {
        $inArray = ['foo', 'bar'];
        $argv = new ArgvWrapper($inArray);
        $outArray = iterator_to_array($argv);
        $this->assertEquals($inArray, $outArray, 'Wrapper can be cast back to array');

    }
}
