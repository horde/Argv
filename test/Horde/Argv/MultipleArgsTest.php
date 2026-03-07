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

class MultipleArgsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
        $this->parser->addOption(
            "-p",
            "--point",
            ['action' => "store", 'nargs' => 3, 'type' => "float", 'dest' => "point"]
        );
    }

    public function testNargsWithPositionalArgs()
    {
        $this->assertParseOK(
            ["foo", "-p", "1", "2.5", "-4.3", "xyz"],
            ['point' => [1.0, 2.5, -4.3]],
            ['foo', 'xyz']
        );
    }

    public function testNargsLongOpt()
    {
        $this->assertParseOK(
            ["--point", "-1", "2.5", "-0", "xyz"],
            ['point' => [-1.0, 2.5, -0.0]],
            ["xyz"]
        );
    }

    public function testNargsInvalidFloatValue()
    {
        $this->assertParseFail(
            ["-p", "1.0", "2x", "3.5"],
            "option -p: invalid floating-point value: '2x'"
        );
    }

    public function testNargsRequiredValues()
    {
        $this->assertParseFail(
            ["--point", "1.0", "3.5"],
            "--point option requires 3 arguments"
        );
    }

}
