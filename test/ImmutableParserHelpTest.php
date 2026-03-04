<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <lang@b1-systems.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Test;

use Horde\Argv\ImmutableParser;
use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder, GroupBuilder};
use Horde\Argv\Modern\Help\HelpFormatter;
use Horde\Argv\Modern\Enum\OptionType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImmutableParser::class)]
class ImmutableParserHelpTest extends TestCase
{
    public function testParserCanFormatHelp(): void
    {
        $parser = ParserBuilder::create()
            ->withUsage('%prog [options]')
            ->withProg('myapp')
            ->build();

        $help = $parser->formatHelp();

        $this->assertIsString($help);
        $this->assertStringContainsString('Usage: myapp', $help);
    }

    public function testParserFormatHelpWithOptions(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->help('Enable verbose output')
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options]')
            ->withProg('test')
            ->addOption($option)
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('-v', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('Enable verbose output', $help);
    }

    public function testParserFormatHelpWithGroups(): void
    {
        $debugOption = OptionBuilder::create()
            ->short('-d')
            ->flag()
            ->help('Debug mode')
            ->build();

        $group = GroupBuilder::create('Debugging')
            ->addOption($debugOption)
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog')
            ->withProg('test')
            ->addGroup($group)
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('Debugging:', $help);
        $this->assertStringContainsString('-d', $help);
    }

    public function testParserFormatHelpWithCustomFormatter(): void
    {
        $parser = ParserBuilder::create()
            ->withUsage('%prog [options]')
            ->withProg('test')
            ->build();

        $formatter = HelpFormatter::create()->withWidth(40);
        $help = $parser->formatHelp($formatter);

        $this->assertIsString($help);
        $this->assertStringContainsString('test', $help);
    }

    public function testParserFormatHelpWithDescription(): void
    {
        $parser = ParserBuilder::create()
            ->withUsage('%prog')
            ->withProg('test')
            ->withDescription('This is a test program for demonstrations')
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('This is a test program', $help);
    }

    public function testParserFormatHelpWithEpilog(): void
    {
        $parser = ParserBuilder::create()
            ->withUsage('%prog')
            ->withProg('test')
            ->withEpilog('Report bugs to bugs@example.com')
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('Report bugs', $help);
    }

    public function testParserFormatHelpCompleteExample(): void
    {
        $verbose = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->counter()
            ->help('Increase verbosity (-v, -vv, -vvv)')
            ->build();

        $port = OptionBuilder::create()
            ->short('-p')
            ->long('--port')
            ->type(OptionType::Int)
            ->default(8080)
            ->help('Server port number')
            ->build();

        $format = OptionBuilder::create()
            ->short('-f')
            ->long('--format')
            ->choices(['json', 'xml', 'csv'])
            ->default('json')
            ->help('Output format')
            ->build();

        $debugOption = OptionBuilder::create()
            ->short('-d')
            ->long('--debug')
            ->flag()
            ->help('Enable debug logging')
            ->build();

        $traceOption = OptionBuilder::create()
            ->short('-t')
            ->long('--trace')
            ->flag()
            ->help('Enable trace output')
            ->build();

        $debugGroup = GroupBuilder::create('Debugging Options')
            ->withDescription('Options for troubleshooting and development')
            ->addOption($debugOption)
            ->addOption($traceOption)
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <input> <output>')
            ->withProg('dataconverter')
            ->withDescription('Convert data files between different formats')
            ->withVersion('1.0.0')
            ->withEpilog('For more information and examples, visit https://example.com/docs')
            ->addOption($verbose)
            ->addOption($port)
            ->addOption($format)
            ->addGroup($debugGroup)
            ->build();

        $help = $parser->formatHelp();

        // Verify all major sections
        $this->assertStringContainsString('Usage: dataconverter', $help);
        $this->assertStringContainsString('Convert data files', $help);
        $this->assertStringContainsString('Options:', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('--port', $help);
        $this->assertStringContainsString('--format', $help);
        $this->assertStringContainsString('(default: 8080)', $help);
        $this->assertStringContainsString('(default: json)', $help);
        $this->assertStringContainsString("(choices: 'json', 'xml', 'csv')", $help);
        $this->assertStringContainsString('Debugging Options:', $help);
        $this->assertStringContainsString('--debug', $help);
        $this->assertStringContainsString('--trace', $help);
        $this->assertStringContainsString('https://example.com/docs', $help);
    }

    public function testParserFormatHelpUsesBuilderFormatter(): void
    {
        $formatter = HelpFormatter::create()->withWidth(60);

        $parser = ParserBuilder::create()
            ->withUsage('%prog')
            ->withProg('test')
            ->withHelpFormatter($formatter)
            ->build();

        $help = $parser->formatHelp();

        $this->assertIsString($help);
    }
}
