<?php

namespace Horde\Argv;

use Horde_Argv_Parser;
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

class OptionChecksTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new Horde_Argv_Parser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE]);
    }

    public function assertOptionError($expected_message, $args)
    {
        $this->assertRaises([$this, 'makeOption'], $args, 'Horde_Argv_OptionException', $expected_message);
    }

    public function testOptStringEmpty()
    {
        $this->expectException('InvalidArgumentException');
        new Horde_Argv_Option();
    }

    public function testOptStringTooShort()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "invalid option string 'b': must be at least two characters long",
            ["b"]
        );
    }

    public function testOptStringShortInvalid()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "invalid short option string '--': must be "
            . "of the form -x, (x any non-dash char)",
            ["--"]
        );
    }

    public function testOptStringLongInvalid()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "invalid long option string '---': "
            . "must start with --, followed by non-dash",
            ["---"]
        );
    }

    public function testAttrInvalid()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: invalid keyword arguments: bar, foo",
            ["-b", ['foo' => null, 'bar' => null]]
        );
    }

    public function testActionInvalid()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: invalid action: 'foo'",
            ["-b", ['action' => 'foo']]
        );
    }

    public function testTypeInvalid()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: invalid option type: 'foo'",
            ["-b", ['type' => 'foo']]
        );
    }

    public function testNoTypeForAction()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: must not supply a type for action 'count'",
            ["-b", ['action' => 'count', 'type' => 'int']]
        );
    }

    public function testNoChoicesList()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b/--bad: must supply a list of "
            . "choices for type 'choice'",
            ["-b", "--bad", ['type' => "choice"]]
        );
    }

    public function testBadChoicesList()
    {
        $this->expectException('Horde_Argv_OptionException');
        $typename = gettype('');
        $this->assertOptionError(
            sprintf("option -b/--bad: choices must be a list of "
                    . "strings ('%s' supplied)", $typename),
            ["-b", "--bad", ['type' => 'choice', 'choices' => 'bad choices']]
        );
    }

    public function testNoChoicesForType()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: must not supply choices for type 'int'",
            ["-b", ['type' => 'int', 'choices' => "bad"]]
        );
    }

    public function testNoConstForAction()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: 'const' must not be supplied for action 'store'",
            ["-b", ['action' => 'store', 'const' => 1]]
        );
    }

    public function testNoNargsForAction()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: 'nargs' must not be supplied for action 'count'",
            ["-b", ['action' => 'count', 'nargs' => 2]]
        );
    }

    public function testCallbackNotCallable()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: callback not callable: 'foo'",
            ["-b", ['action' => 'callback', 'callback' => 'foo']]
        );
    }

    public function dummy() {}

    public function testCallbackArgsNoArray()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: callbackArgs, if supplied, "
            . "must be an array: not 'foo'",
            ["-b", ['action' => 'callback',
                'callback' => [$this, 'dummy'],
                'callbackArgs' => 'foo']]
        );
    }

    public function testNoCallbackForAction()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: callback supplied ('foo') for non-callback option",
            ["-b", ['action' => 'store',
                'callback' => 'foo']]
        );
    }

    public function testNoCallbackArgsForAction()
    {
        $this->expectException('Horde_Argv_OptionException');
        $this->assertOptionError(
            "option -b: callbackArgs supplied for non-callback option",
            ["-b", ['action' => 'store',
                'callbackArgs' => 'foo']]
        );
    }

}
