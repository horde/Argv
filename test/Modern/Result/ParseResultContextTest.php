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

namespace Horde\Argv\Test\Modern\Result;

use Horde\Argv\Modern\Result\{ParseResult, OptionValues};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for ParseResult with context support.
 *
 * @category Horde
 * @package  Argv
 */
#[CoversClass(ParseResult::class)]
class ParseResultContextTest extends TestCase
{
    public function testLegacyModeWithoutContext(): void
    {
        $options = new OptionValues(['verbose' => true, 'port' => 8080]);
        $result = new ParseResult(
            options: $options,
            arguments: ['file.txt'],
            unknown: []
        );

        $this->assertFalse($result->hasContext());
        $this->assertNull($result->getContext());
        $this->assertNull($result->globalOptions);
        $this->assertNull($result->contextOptions);

        // Legacy access works
        $this->assertTrue($result->options->get('verbose'));
        $this->assertSame(8080, $result->options->get('port'));

        // Convenience methods work in legacy mode
        $this->assertTrue($result->getOption('verbose'));
        $this->assertSame(8080, $result->getOption('port'));
        $this->assertTrue($result->hasOption('verbose'));
        $this->assertFalse($result->hasOption('missing'));
    }

    public function testContextModeWithActiveContext(): void
    {
        $global = new OptionValues(['verbose' => true]);
        $context = new OptionValues(['force' => true, 'timeout' => 60]);

        $result = new ParseResult(
            options: new OptionValues([]),  // Not used in context mode
            arguments: ['production'],
            unknown: [],
            globalOptions: $global,
            contextOptions: $context,
            context: 'deploy'
        );

        $this->assertTrue($result->hasContext());
        $this->assertSame('deploy', $result->getContext());
        $this->assertSame($global, $result->globalOptions);
        $this->assertSame($context, $result->contextOptions);

        // Context options accessible
        $this->assertTrue($result->contextOptions->get('force'));
        $this->assertSame(60, $result->contextOptions->get('timeout'));

        // Global options accessible
        $this->assertTrue($result->globalOptions->get('verbose'));
    }

    public function testGetOptionPriority(): void
    {
        // Context option should take priority over global
        $global = new OptionValues(['verbose' => false, 'mode' => 'global']);
        $context = new OptionValues(['verbose' => true]);

        $result = new ParseResult(
            options: new OptionValues([]),
            arguments: [],
            globalOptions: $global,
            contextOptions: $context,
            context: 'deploy'
        );

        // Context value takes priority
        $this->assertTrue($result->getOption('verbose'));

        // Falls back to global when not in context
        $this->assertSame('global', $result->getOption('mode'));

        // Returns default when missing
        $this->assertSame('default', $result->getOption('missing', 'default'));
    }

    public function testHasOptionInContextMode(): void
    {
        $global = new OptionValues(['global_opt' => 'value']);
        $context = new OptionValues(['context_opt' => 'value']);

        $result = new ParseResult(
            options: new OptionValues([]),
            arguments: [],
            globalOptions: $global,
            contextOptions: $context,
            context: 'deploy'
        );

        $this->assertTrue($result->hasOption('context_opt'));
        $this->assertTrue($result->hasOption('global_opt'));
        $this->assertFalse($result->hasOption('missing'));
    }

    public function testContextModeWithoutGlobalOptions(): void
    {
        $context = new OptionValues(['force' => true]);

        $result = new ParseResult(
            options: new OptionValues([]),
            arguments: ['production'],
            globalOptions: null,
            contextOptions: $context,
            context: 'deploy'
        );

        $this->assertTrue($result->hasContext());
        $this->assertTrue($result->getOption('force'));
        $this->assertNull($result->getOption('missing'));
    }

    public function testBackwardCompatibleArguments(): void
    {
        $options = new OptionValues(['verbose' => true]);

        // Legacy mode
        $result1 = new ParseResult(
            options: $options,
            arguments: ['file1.txt', 'file2.txt']
        );

        $this->assertTrue($result1->hasArguments());
        $this->assertCount(2, $result1->arguments);
        $this->assertSame('file1.txt', $result1->arguments[0]);

        // Context mode
        $result2 = new ParseResult(
            options: new OptionValues([]),
            arguments: ['production'],
            globalOptions: new OptionValues([]),
            contextOptions: new OptionValues(['force' => true]),
            context: 'deploy'
        );

        $this->assertTrue($result2->hasArguments());
        $this->assertCount(1, $result2->arguments);
        $this->assertSame('production', $result2->arguments[0]);
    }

    public function testUnknownOptions(): void
    {
        $result = new ParseResult(
            options: new OptionValues([]),
            arguments: [],
            unknown: ['--unknown', '--also-unknown']
        );

        $this->assertTrue($result->hasUnknown());
        $this->assertCount(2, $result->unknown);
        $this->assertSame('--unknown', $result->unknown[0]);
    }

    public function testNoContextNoGlobalNoContextOptions(): void
    {
        // Minimal result
        $options = new OptionValues([]);
        $result = new ParseResult(
            options: $options,
            arguments: []
        );

        $this->assertFalse($result->hasContext());
        $this->assertFalse($result->hasArguments());
        $this->assertFalse($result->hasUnknown());
        $this->assertNull($result->getContext());
    }
}
