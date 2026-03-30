<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Test\Modern\Help;

use Horde\Argv\Modern\Help\HelpFormatter;
use Horde\Argv\Modern\Config\{ParserConfig, OptionConfig, OptionGroupConfig};
use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder, GroupBuilder};
use Horde\Argv\Modern\Enum\{OptionType, OptionAction};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HelpFormatter::class)]
class HelpFormatterTest extends TestCase
{
    public function testFormatterCanBeCreated(): void
    {
        $formatter = HelpFormatter::create();
        $this->assertInstanceOf(HelpFormatter::class, $formatter);
    }

    public function testFormatterIsImmutable(): void
    {
        $formatter1 = HelpFormatter::create();
        $formatter2 = $formatter1->withWidth(100);

        $this->assertNotSame($formatter1, $formatter2);
        $this->assertSame(80, $formatter1->getWidth());
        $this->assertSame(100, $formatter2->getWidth());
    }

    public function testFormatUsageLine(): void
    {
        $config = new ParserConfig(
            usage: '%prog [options] <file>',
            prog: 'myapp'
        );

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config);

        $this->assertStringContainsString('Usage: myapp [options] <file>', $help);
    }

    public function testFormatDescription(): void
    {
        $config = new ParserConfig(
            usage: '%prog',
            prog: 'test',
            description: 'This is a test program'
        );

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config);

        $this->assertStringContainsString('This is a test program', $help);
    }

    public function testFormatEpilog(): void
    {
        $config = new ParserConfig(
            usage: '%prog',
            prog: 'test',
            epilog: 'Report bugs to bugs@example.com'
        );

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config);

        $this->assertStringContainsString('Report bugs to bugs@example.com', $help);
    }

    public function testFormatSimpleOption(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->help('Enable verbose output')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('-v', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('Enable verbose output', $help);
    }

    public function testFormatOptionWithArgument(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-p')
            ->long('--port')
            ->type(OptionType::Int)
            ->dest('port')
            ->help('Server port')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('-p PORT', $help);
        $this->assertStringContainsString('--port=PORT', $help);
        $this->assertStringContainsString('Server port', $help);
    }

    public function testFormatOptionWithMetavar(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-f')
            ->long('--file')
            ->metavar('FILE')
            ->help('Input file')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('-f FILE', $help);
        $this->assertStringContainsString('--file=FILE', $help);
    }

    public function testFormatOptionWithDefault(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->dest('port')
            ->default(8080)
            ->help('Server port')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('(default: 8080)', $help);
    }

    public function testFormatOptionWithChoices(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-f')
            ->dest('format')
            ->choices(['json', 'xml', 'csv'])
            ->help('Output format')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString("(choices: 'json', 'xml', 'csv')", $help);
    }

    public function testFormatOptionGroup(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');

        $debugOption = OptionBuilder::create()
            ->short('-d')
            ->long('--debug')
            ->flag()
            ->help('Enable debug mode')
            ->build();

        $group = GroupBuilder::create('Debugging Options')
            ->withDescription('Options for debugging')
            ->addOption($debugOption)
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [], [$group]);

        $this->assertStringContainsString('Debugging Options:', $help);
        $this->assertStringContainsString('Options for debugging', $help);
        $this->assertStringContainsString('--debug', $help);
    }

    public function testFormatMultipleOptions(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');

        $verbose = OptionBuilder::create()->short('-v')->flag()->help('Verbose')->build();
        $debug = OptionBuilder::create()->short('-d')->flag()->help('Debug')->build();
        $quiet = OptionBuilder::create()->short('-q')->flag()->help('Quiet')->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$verbose, $debug, $quiet]);

        $this->assertStringContainsString('-v', $help);
        $this->assertStringContainsString('-d', $help);
        $this->assertStringContainsString('-q', $help);
        $this->assertStringContainsString('Verbose', $help);
        $this->assertStringContainsString('Debug', $help);
        $this->assertStringContainsString('Quiet', $help);
    }

    public function testFormatCompleteHelp(): void
    {
        $config = new ParserConfig(
            usage: '%prog [options] <input> <output>',
            prog: 'converter',
            description: 'Convert files between formats',
            epilog: 'For more information, visit https://example.com'
        );

        $verbose = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->counter()
            ->help('Increase verbosity')
            ->build();

        $format = OptionBuilder::create()
            ->short('-f')
            ->long('--format')
            ->dest('format')
            ->choices(['json', 'xml'])
            ->default('json')
            ->help('Output format')
            ->build();

        $debugOption = OptionBuilder::create()
            ->short('-d')
            ->long('--debug')
            ->flag()
            ->help('Enable debug mode')
            ->build();

        $group = GroupBuilder::create('Debugging')
            ->addOption($debugOption)
            ->build();

        $formatter = HelpFormatter::create()->withWidth(80);
        $help = $formatter->format($config, [$verbose, $format], [$group]);

        // Check all sections present
        $this->assertStringContainsString('Usage: converter', $help);
        $this->assertStringContainsString('Convert files between formats', $help);
        $this->assertStringContainsString('Options:', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('--format', $help);
        $this->assertStringContainsString('Debugging:', $help);
        $this->assertStringContainsString('--debug', $help);
        $this->assertStringContainsString('https://example.com', $help);
    }

    public function testCustomWidth(): void
    {
        $formatter = HelpFormatter::create()->withWidth(40);
        $this->assertSame(40, $formatter->getWidth());
    }

    public function testCustomIndent(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()->short('-v')->flag()->help('Test')->build();

        $formatter = HelpFormatter::create()->withIndent(4);
        $help = $formatter->format($config, [$option]);

        // Options should be indented with custom indent
        $this->assertStringContainsString('    -v', $help);
    }

    public function testShortFirstOrdering(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->build();

        $formatterShortFirst = HelpFormatter::create()->withShortFirst(true);
        $helpShortFirst = $formatterShortFirst->format($config, [$option]);

        $formatterLongFirst = HelpFormatter::create()->withShortFirst(false);
        $helpLongFirst = $formatterLongFirst->format($config, [$option]);

        // Both should contain both options
        $this->assertStringContainsString('-v', $helpShortFirst);
        $this->assertStringContainsString('--verbose', $helpShortFirst);
        $this->assertStringContainsString('-v', $helpLongFirst);
        $this->assertStringContainsString('--verbose', $helpLongFirst);
    }

    public function testBuildMethod(): void
    {
        $formatter = HelpFormatter::create()
            ->withWidth(100)
            ->withIndent(4)
            ->build();

        $this->assertInstanceOf(HelpFormatter::class, $formatter);
        $this->assertSame(100, $formatter->getWidth());
    }

    public function testFormatOptionWithoutHelp(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('-v', $help);
        $this->assertStringContainsString('--verbose', $help);
    }

    public function testFormatShortOnlyOption(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->help('Verbose mode')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('-v', $help);
        $this->assertStringContainsString('Verbose mode', $help);
    }

    public function testFormatLongOnlyOption(): void
    {
        $config = new ParserConfig(usage: '%prog', prog: 'test');
        $option = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->help('Verbose mode')
            ->build();

        $formatter = HelpFormatter::create();
        $help = $formatter->format($config, [$option]);

        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('Verbose mode', $help);
    }
}
