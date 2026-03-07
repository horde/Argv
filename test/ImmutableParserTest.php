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
use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder};
use Horde\Argv\Modern\Enum\{OptionAction, OptionType};
use Horde\Argv\Modern\Exception\{
    InvalidOptionException,
    AmbiguousOptionException,
    MissingValueException
};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImmutableParser::class)]
class ImmutableParserTest extends TestCase
{
    public function testParserCanBeConstructed(): void
    {
        $parser = ParserBuilder::create()->build();
        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testParseEmptyArguments(): void
    {
        $parser = ParserBuilder::create()->build();
        $result = $parser->parse([]);

        $this->assertCount(0, $result->arguments);
        $this->assertCount(0, $result->unknown);
    }

    public function testParseFlagOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-v']);

        $this->assertTrue($result->options->get('v'));
        $this->assertCount(0, $result->arguments);
    }

    public function testParseLongOption(): void
    {
        $option = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['--verbose']);

        $this->assertTrue($result->options->get('verbose'));
    }

    public function testParseOptionWithValue(): void
    {
        $option = OptionBuilder::create()
            ->short('-n')
            ->long('--name')
            ->dest('name')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['--name', 'Alice']);

        $this->assertSame('Alice', $result->options->get('name'));
    }

    public function testParseOptionWithEqualsValue(): void
    {
        $option = OptionBuilder::create()
            ->long('--name')
            ->dest('name')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['--name=Bob']);

        $this->assertSame('Bob', $result->options->get('name'));
    }

    public function testParseShortOptionWithAttachedValue(): void
    {
        $option = OptionBuilder::create()
            ->short('-n')
            ->dest('name')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-nCharlie']);

        $this->assertSame('Charlie', $result->options->get('name'));
    }

    public function testParseMultipleShortFlags(): void
    {
        $verbose = OptionBuilder::create()->short('-v')->flag()->build();
        $debug = OptionBuilder::create()->short('-d')->flag()->build();
        $trace = OptionBuilder::create()->short('-t')->flag()->build();

        $parser = ParserBuilder::create()
            ->addOption($verbose)
            ->addOption($debug)
            ->addOption($trace)
            ->build();

        $result = $parser->parse(['-vdt']);

        $this->assertTrue($result->options->get('v'));
        $this->assertTrue($result->options->get('d'));
        $this->assertTrue($result->options->get('t'));
    }

    public function testParsePositionalArguments(): void
    {
        $parser = ParserBuilder::create()->build();
        $result = $parser->parse(['file1.txt', 'file2.txt']);

        $this->assertSame(['file1.txt', 'file2.txt'], $result->arguments);
    }

    public function testParseMixedOptionsAndArguments(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-v', 'file.txt']);

        $this->assertTrue($result->options->get('v'));
        $this->assertSame(['file.txt'], $result->arguments);
    }

    public function testParseStopsAtDoubleDash(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-v', '--', '-d', 'file.txt']);

        $this->assertTrue($result->options->get('v'));
        $this->assertSame(['-d', 'file.txt'], $result->arguments);
    }

    public function testParseIntegerType(): void
    {
        $option = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->dest('port')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-p', '8080']);

        $this->assertSame(8080, $result->options->get('port'));
        $this->assertIsInt($result->options->get('port'));
    }

    public function testParseFloatType(): void
    {
        $option = OptionBuilder::create()
            ->short('-r')
            ->type(OptionType::Float)
            ->dest('rate')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-r', '3.14']);

        $this->assertSame(3.14, $result->options->get('rate'));
        $this->assertIsFloat($result->options->get('rate'));
    }

    public function testParseCounterOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->counter()
            ->dest('verbosity')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-v', '-v', '-v']);

        $this->assertSame(3, $result->options->get('verbosity'));
    }

    public function testParseAppendOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-i')
            ->repeatable()
            ->dest('include')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-i', 'path1', '-i', 'path2']);

        $this->assertSame(['path1', 'path2'], $result->options->get('include'));
    }

    public function testParseDefaultValue(): void
    {
        $option = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->dest('port')
            ->default(8080)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse([]);

        $this->assertSame(8080, $result->options->get('port'));
    }

    public function testParseOverridesDefault(): void
    {
        $option = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->dest('port')
            ->default(8080)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-p', '3000']);

        $this->assertSame(3000, $result->options->get('port'));
    }

    public function testParseEnforcesChoices(): void
    {
        $option = OptionBuilder::create()
            ->short('-f')
            ->dest('format')
            ->choices(['json', 'xml', 'csv'])
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('must be one of');

        $parser->parse(['-f', 'yaml']);
    }

    public function testParseAcceptsValidChoice(): void
    {
        $option = OptionBuilder::create()
            ->short('-f')
            ->dest('format')
            ->choices(['json', 'xml', 'csv'])
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-f', 'json']);

        $this->assertSame('json', $result->options->get('format'));
    }

    public function testParseThrowsOnUnknownOption(): void
    {
        $parser = ParserBuilder::create()->build();

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Unknown option');

        $parser->parse(['--unknown']);
    }

    public function testParseAllowsUnknownOptions(): void
    {
        $parser = ParserBuilder::create()
            ->allowUnknownArgs(true)
            ->build();

        $result = $parser->parse(['--unknown', 'value']);

        $this->assertContains('--unknown', $result->unknown);
    }

    public function testParseIgnoresUnknownOptions(): void
    {
        $parser = ParserBuilder::create()
            ->ignoreUnknownArgs(true)
            ->build();

        $result = $parser->parse(['--unknown', 'value']);

        $this->assertEmpty($result->unknown);
    }

    public function testParseThrowsOnMissingArgument(): void
    {
        $option = OptionBuilder::create()
            ->short('-n')
            ->dest('name')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('requires a value');

        $parser->parse(['-n']);
    }

    public function testParseCallbackOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-u')
            ->dest('username')
            ->callback(fn($v) => strtoupper($v))
            ->action(OptionAction::Callback)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-u', 'alice']);

        $this->assertSame('ALICE', $result->options->get('username'));
    }

    public function testParseValidatorOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->dest('port')
            ->validator(fn($v) => $v > 0 && $v < 65536 ? true : 'Port must be 1-65535')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Port must be 1-65535');

        $parser->parse(['-p', '99999']);
    }

    public function testParseMapTransformer(): void
    {
        $option = OptionBuilder::create()
            ->short('-e')
            ->dest('email')
            ->map(fn($v) => strtolower(trim($v)))
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['-e', '  ALICE@EXAMPLE.COM  ']);

        $this->assertSame('alice@example.com', $result->options->get('email'));
    }

    public function testParseAmbiguousLongOption(): void
    {
        $option1 = OptionBuilder::create()->long('--verbose')->flag()->build();
        $option2 = OptionBuilder::create()->long('--version')->flag()->build();

        $parser = ParserBuilder::create()
            ->addOption($option1)
            ->addOption($option2)
            ->build();

        $this->expectException(AmbiguousOptionException::class);
        $this->expectExceptionMessage('could match');

        $parser->parse(['--ver']);
    }

    public function testParsePartialLongOptionMatch(): void
    {
        $option = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $result = $parser->parse(['--verb']);

        $this->assertTrue($result->options->get('verbose'));
    }

    public function testParseInterspersedArgsEnabled(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->allowInterspersedArgs(true)
            ->build();

        $result = $parser->parse(['file1', '-v', 'file2']);

        $this->assertTrue($result->options->get('v'));
        $this->assertSame(['file1', 'file2'], $result->arguments);
    }

    public function testParseInterspersedArgsDisabled(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->allowInterspersedArgs(false)
            ->build();

        $result = $parser->parse(['file1', '-v', 'file2']);

        $this->assertNull($result->options->get('v'));
        $this->assertSame(['file1', '-v', 'file2'], $result->arguments);
    }

    public function testParseComplexScenario(): void
    {
        $verbose = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->counter()
            ->dest('verbosity')
            ->build();

        $port = OptionBuilder::create()
            ->short('-p')
            ->long('--port')
            ->type(OptionType::Int)
            ->dest('port')
            ->default(8080)
            ->build();

        $format = OptionBuilder::create()
            ->short('-f')
            ->long('--format')
            ->dest('format')
            ->choices(['json', 'xml'])
            ->default('json')
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($verbose)
            ->addOption($port)
            ->addOption($format)
            ->build();

        $result = $parser->parse([
            '-vv',
            '--port=3000',
            '--format', 'xml',
            'input.txt',
            'output.txt',
        ]);

        $this->assertSame(2, $result->options->get('verbosity'));
        $this->assertSame(3000, $result->options->get('port'));
        $this->assertSame('xml', $result->options->get('format'));
        $this->assertSame(['input.txt', 'output.txt'], $result->arguments);
    }

    public function testToBuilderReturnsBuilder(): void
    {
        $option = OptionBuilder::create()->short('-v')->flag()->build();
        $parser = ParserBuilder::create()->addOption($option)->build();

        $builder = $parser->toBuilder();

        $this->assertInstanceOf(ParserBuilder::class, $builder);
    }

    public function testToBuilderEnablesUseAndAmend(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->flag()->build();
        $parser1 = ParserBuilder::create()
            ->addOption($option1)
            ->allowUnknownArgs(true)  // Allow unknown for parser1
            ->build();

        $option2 = OptionBuilder::create()->short('-d')->flag()->build();
        $parser2 = $parser1->toBuilder()->addOption($option2)->build();

        // Original parser sees -d as unknown
        $result1 = $parser1->parse(['-v', '-d']);
        $this->assertTrue($result1->options->get('v'));
        $this->assertContains('-d', $result1->unknown);

        // New parser has both options
        $result2 = $parser2->parse(['-v', '-d']);
        $this->assertTrue($result2->options->get('v'));
        $this->assertTrue($result2->options->get('d'));
    }
}
