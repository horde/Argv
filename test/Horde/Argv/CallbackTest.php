<?php

namespace Horde\Argv;

use Horde_Argv_Option;
use Horde_Argv_Parser;
use Horde_Argv_IndentedHelpFormatter;
use Horde_Cli_Color;

/**
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @author     Mike Naberezny <mike@maintainable.com>
 * @license    http://www.horde.org/licenses/bsd BSD
 * @category   Horde
 * @package    Argv
 * @subpackage UnitTests
 * @coversNothing
 */

class CallbackTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $options = [
            new Horde_Argv_Option(
                '-x',
                null,
                ['action' => 'callback', 'callback' => [$this, 'processOpt']]
            ),
            new Horde_Argv_Option(
                '-f',
                '--file',
                ['action' => 'callback',
                    'callback' => [$this, 'processOpt'],
                    'type' => 'string',
                    'dest' => 'filename']
            ),
        ];

        $this->parser = new Horde_Argv_Parser(['optionList' => $options]);
    }

    public function processOpt($option, $opt, $value, $parser_)
    {
        if ($opt == '-x') {
            $this->assertEquals(['-x'], $option->shortOpts);
            $this->assertEquals([], $option->longOpts);
            $this->assertInstanceOf(get_class($this->parser), $parser_);
            $this->assertNull($value);
            $this->assertEquals(['filename' => null], iterator_to_array($parser_->values));

            $parser_->values->x = 42;
        } elseif ($opt == '--file') {
            $this->assertEquals(['-f'], $option->shortOpts);
            $this->assertEquals(['--file'], $option->longOpts);
            $this->assertInstanceOf(get_class($this->parser), $parser_);
            $this->assertEquals('foo', $value);
            $this->assertEquals(['filename' => null, 'x' => 42], iterator_to_array($parser_->values));

            $parser_->values->{$option->dest} = $value;
        } else {
            $this->fail(sprintf('Unknown option %r in processOpt.', $opt));
        }
    }

    public function testCallback()
    {
        $this->assertParseOk(
            ['-x', '--file=foo'],
            ['filename' => 'foo', 'x' => 42],
            []
        );
    }

    public function testCallbackHelp()
    {
        // This test was prompted by SF bug #960515 -- the point is not to
        // inspect the help text, just to make sure that formatHelp() doesn't
        // crash.
        $parser = new Horde_Argv_Parser([
            'usage' => Horde_Argv_Option::SUPPRESS_USAGE,
            'formatter' => new Horde_Argv_IndentedHelpFormatter(
                2,
                24,
                null,
                true,
                new Horde_Cli_Color(Horde_Cli_Color::FORMAT_NONE)
            ),
        ]);
        $parser->removeOption('-h');
        $parser->addOption(
            '-t',
            '--test',
            [
                'action' => 'callback',
                'callback' => [$this, 'returnNull'],
                'type' => 'string',
                'help' => 'foo',
            ]
        );

        $expectedHelp = "Options:\n  -t TEST, --test=TEST  foo\n";
        $this->assertHelp($parser, $expectedHelp);
    }

    public function returnNull() {}
}
