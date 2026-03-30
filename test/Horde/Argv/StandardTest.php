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

class StandardTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            $this->makeOption('-a', ['type' => 'string']),
            $this->makeOption('-b', '--boo', ['type' => 'int', 'dest' => 'boo']),
            $this->makeOption('--foo', ['action' => 'append']),
        ];

        $this->parser = new InterceptingParser(['usage' => Horde_Argv_Option::SUPPRESS_USAGE,
            'optionList' => $options]);
    }

    public function testRequiredValue()
    {
        $this->assertParseFail(
            ['-a'],
            '-a option requires an argument'
        );
    }

    public function testInvalidInteger()
    {
        $this->assertParseFail(
            ['-b', '5x'],
            "option -b: invalid integer value: '5x'"
        );
    }

    public function testNoSuchOption()
    {
        $this->assertParseFail(
            ['--boo13'],
            "no such option: --boo13"
        );
    }

    public function testLongInvalidInteger()
    {
        $this->assertParseFail(
            ["--boo=x5"],
            "option --boo: invalid integer value: 'x5'"
        );
    }

    public function testEmpty()
    {
        $this->assertParseOk(
            [],
            ['a' => null, 'boo' => null, 'foo' => null],
            []
        );
    }

    public function testShortOptEmptyLongOptAppend()
    {
        $this->assertParseOk(
            ["-a", "", "--foo=blah", "--foo="],
            ['a' => "", 'boo' => null, 'foo' => ["blah", ""]],
            []
        );
    }

    public function testLongOptionAppend()
    {
        $this->assertParseOk(
            ["--foo", "bar", "--foo", "", "--foo=x"],
            ['a' => null,
                'boo' => null,
                'foo' => ['bar', '', 'x']],
            []
        );
    }

    public function testOptionArgumentJoined()
    {
        $this->assertParseOk(
            ["-abc"],
            ['a' => "bc", 'boo' => null, 'foo' => null],
            []
        );
    }

    public function testOptionArgumentSplit()
    {
        $this->assertParseOk(
            ["-a", "34"],
            ['a' => "34", 'boo' => null, 'foo' => null],
            []
        );
    }

    public function testOptionArgumentJoinedInteger()
    {
        $this->assertParseOk(
            ["-b34"],
            ['a' => null, 'boo' => 34, 'foo' => null],
            []
        );
    }

    public function testOptionArgumentSplitNegativeInteger()
    {
        $this->assertParseOk(
            ["-b", "-5"],
            ['a' => null, 'boo' => -5, 'foo' => null],
            []
        );
    }

    public function testLongOptionArgumentJoined()
    {
        $this->assertParseOk(
            ["--boo=13"],
            ['a' => null, 'boo' => 13, 'foo' => null],
            []
        );
    }

    public function testLongOptionArgumentSplit()
    {
        $this->assertParseOk(
            ["--boo", "111"],
            ['a' => null, 'boo' => 111, 'foo' => null],
            []
        );
    }

    public function testLongOptionShortOption()
    {
        $this->assertParseOk(
            ["--foo=bar", "-axyz"],
            ['a' => 'xyz', 'boo' => null, 'foo' => ["bar"]],
            []
        );
    }

    public function testAbbrevLongOption()
    {
        $this->assertParseOk(
            ["--f=bar", "-axyz"],
            ['a' => 'xyz', 'boo' => null, 'foo' => ["bar"]],
            []
        );
    }

    public function testDefaults()
    {
        [$options, $args] = $this->parser->parseArgs([]);
        $defaults = $this->parser->getDefaultValues();

        $this->assertEquals($defaults, $options);
    }

    public function testAmbiguousOption()
    {
        $this->parser->addOption("--foz", ['action' => 'store',
            'type' => 'string', 'dest' => 'foo']);
        $this->assertParseFail(
            ['--f=bar'],
            "ambiguous option: --f (--foo, --foz?)"
        );
    }

    public function testShortAndLongOptionSplit()
    {
        $this->assertParseOk(
            ["-a", "xyz", "--foo", "bar"],
            ['a' => 'xyz', 'boo' => null, 'foo' => ["bar"]],
            []
        );
    }

    public function testShortOptionSplitLongOptionAppend()
    {
        $this->assertParseOk(
            ["--foo=bar", "-b", "123", "--foo", "baz"],
            ['a' => null, 'boo' => 123, 'foo' => ["bar", "baz"]],
            []
        );
    }

    public function testShortOptionSplitOnePositionalArg()
    {
        $this->assertParseOk(
            ["-a", "foo", "bar"],
            ['a' => "foo", 'boo' => null, 'foo' => null],
            ["bar"]
        );
    }

    public function testShortOptionConsumesSeparator()
    {
        $this->assertParseOk(
            ["-a", "--", "foo", "bar"],
            ['a' => "--", 'boo' => null, 'foo' => null],
            ["foo", "bar"]
        );

        $this->assertParseOk(
            ["-a", "--", "--foo", "bar"],
            ['a' => "--", 'boo' => null, 'foo' => ["bar"]],
            []
        );
    }

    public function testShortOptionJoinedAndSeparator()
    {
        $this->assertParseOk(
            ["-ab", "--", "--foo", "bar"],
            ['a' => "b", 'boo' => null, 'foo' => null],
            ["--foo", "bar"]
        );
    }

    public function testHyphenBecomesPositionalArg()
    {
        $this->assertParseOk(
            ["-ab", "-", "--foo", "bar"],
            ['a' => "b", 'boo' => null, 'foo' => ["bar"]],
            ["-"]
        );
    }

    public function testNoAppendVersusAppend()
    {
        $this->assertParseOk(
            ["-b3", "-b", "5", "--foo=bar", "--foo", "baz"],
            ['a' => null, 'boo' => 5, 'foo' => ["bar", "baz"]],
            []
        );
    }

    public function testOptionConsumesOptionLikeString()
    {
        $this->assertParseOk(
            ["-a", "-b3"],
            ['a' => "-b3", 'boo' => null, 'foo' => null],
            []
        );
    }
}
