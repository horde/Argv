<?php

namespace Horde\Argv;

use Horde_Argv_Option;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class MultipleArgsAppendTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
        $this->parser->addOption("-p", "--point", [
            'action' => "store", 'nargs' => 3, 'type' => 'float', 'dest' => 'point']);
        $this->parser->addOption("-f", "--foo", [
            'action' => "append", 'nargs' => 2, 'type' => "int", 'dest' => "foo"]);
        $this->parser->addOption("-z", "--zero", [
            'action' => "append_const", 'dest' => "foo", 'const' => [0, 0]]);
    }

    public function testNargsAppend()
    {
        $this->assertParseOK(
            ["-f", "4", "-3", "blah", "--foo", "1", "666"],
            ['point' => null, 'foo' => [[4, -3], [1, 666]]],
            ['blah']
        );
    }

    public function testNargsAppendRequiredValues()
    {
        $this->assertParseFail(
            ["-f4,3"],
            "-f option requires 2 arguments"
        );
    }

    public function testNargsAppendSimple()
    {
        $this->assertParseOK(
            ["--foo=3", "4"],
            ['point' => null, 'foo' => [[3, 4]]],
            []
        );
    }

    public function testNargsAppendConst()
    {
        $this->assertParseOK(
            ["--zero", "--foo", "3", "4", "-z"],
            ['point' => null, 'foo' => [[0, 0], [3, 4], [0, 0]]],
            []
        );
    }

}
