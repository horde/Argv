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

namespace Horde\Argv\Test;

use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder, ContextBuilder};
use Horde\Argv\Modern\Exception\InvalidArgumentCountException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for context parsing (Phase 2).
 *
 * @category Horde
 * @package  Argv
 */
#[CoversClass(\Horde\Argv\ImmutableParser::class)]
class ContextParsingTest extends TestCase
{
    public function testBasicContextDetection(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application')
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['deploy']);

        $this->assertTrue($result->hasContext());
        $this->assertSame('deploy', $result->getContext());
        $this->assertCount(0, $result->arguments);
    }

    public function testContextWithArguments(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->requiresArguments(1, 'environment')
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['deploy', 'production']);

        $this->assertSame('deploy', $result->getContext());
        $this->assertCount(1, $result->arguments);
        $this->assertSame('production', $result->arguments[0]);
    }

    public function testContextWithMultipleArguments(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->acceptsArguments(1, 3)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['deploy', 'production', 'v2.0', 'now']);

        $this->assertSame('deploy', $result->getContext());
        $this->assertCount(3, $result->arguments);
        $this->assertSame(['production', 'v2.0', 'now'], $result->arguments);
    }

    public function testGlobalOptions(): void
    {
        $verboseOpt = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->build();

        $deployContext = ContextBuilder::create('deploy')->build();

        $parser = ParserBuilder::create()
            ->addOption($verboseOpt)
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['--verbose', 'deploy']);

        $this->assertTrue($result->hasContext());
        $this->assertSame('deploy', $result->getContext());
        $this->assertTrue($result->globalOptions->get('verbose'));
    }

    public function testContextSpecificOptions(): void
    {
        $forceOpt = OptionBuilder::create()
            ->long('--force')
            ->flag()
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->addOption($forceOpt)
            ->requiresArguments(1)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['deploy', '--force', 'production']);

        $this->assertSame('deploy', $result->getContext());
        $this->assertTrue($result->contextOptions->get('force'));
        $this->assertSame('production', $result->arguments[0]);
    }

    public function testGlobalAndContextOptions(): void
    {
        $globalVerbose = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->build();

        $contextForce = OptionBuilder::create()
            ->long('--force')
            ->flag()
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->addOption($contextForce)
            ->requiresArguments(1)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($globalVerbose)
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['--verbose', 'deploy', '--force', 'production']);

        $this->assertTrue($result->globalOptions->get('verbose'));
        $this->assertTrue($result->contextOptions->get('force'));
        $this->assertSame('production', $result->arguments[0]);
    }

    public function testGetOptionFallback(): void
    {
        $globalVerbose = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->build();

        $contextForce = OptionBuilder::create()
            ->long('--force')
            ->flag()
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->addOption($contextForce)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($globalVerbose)
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['--verbose', 'deploy', '--force']);

        // getOption() checks context first, then global
        $this->assertTrue($result->getOption('force')); // From context
        $this->assertTrue($result->getOption('verbose')); // From global
        $this->assertNull($result->getOption('missing')); // Not found
    }

    public function testContextAlias(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withAliases(['dep'])
            ->requiresArguments(1)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        // Using alias
        $result1 = $parser->parse(['dep', 'production']);
        $this->assertSame('deploy', $result1->getContext()); // Returns primary name
        $this->assertSame('production', $result1->arguments[0]);

        // Using primary name
        $result2 = $parser->parse(['deploy', 'production']);
        $this->assertSame('deploy', $result2->getContext());
    }

    public function testMultipleContexts(): void
    {
        $deployContext = ContextBuilder::create('deploy')->build();
        $rollbackContext = ContextBuilder::create('rollback')->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->addContext($rollbackContext)
            ->build();

        $result1 = $parser->parse(['deploy']);
        $this->assertSame('deploy', $result1->getContext());

        $result2 = $parser->parse(['rollback']);
        $this->assertSame('rollback', $result2->getContext());
    }

    public function testNoContextDetected(): void
    {
        $verboseOpt = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->build();

        $deployContext = ContextBuilder::create('deploy')->build();

        $parser = ParserBuilder::create()
            ->addOption($verboseOpt)
            ->addContext($deployContext)
            ->build();

        // No context in args
        $result = $parser->parse(['--verbose', 'file.txt']);

        $this->assertFalse($result->hasContext());
        $this->assertNull($result->getContext());
        $this->assertTrue($result->globalOptions->get('verbose'));
        $this->assertSame(['file.txt'], $result->arguments);
    }

    public function testArgumentCountValidationTooFew(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->requiresArguments(2, 'environment version')
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $this->expectException(InvalidArgumentCountException::class);
        $this->expectExceptionMessage("requires exactly 2 arguments, got 1");

        $parser->parse(['deploy', 'production']);
    }

    public function testArgumentCountValidationTooMany(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->requiresExactly(1)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $this->expectException(InvalidArgumentCountException::class);
        $this->expectExceptionMessage("requires exactly 1 argument, got 2");

        $parser->parse(['deploy', 'production', 'extra']);
    }

    public function testArgumentCountValidationRange(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->acceptsArguments(1, 3)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        // Valid: 1 argument
        $result1 = $parser->parse(['deploy', 'production']);
        $this->assertCount(1, $result1->arguments);

        // Valid: 2 arguments
        $result2 = $parser->parse(['deploy', 'production', 'v2.0']);
        $this->assertCount(2, $result2->arguments);

        // Valid: 3 arguments
        $result3 = $parser->parse(['deploy', 'production', 'v2.0', 'now']);
        $this->assertCount(3, $result3->arguments);

        // Invalid: 0 arguments
        $this->expectException(InvalidArgumentCountException::class);
        $parser->parse(['deploy']);
    }

    public function testContextOptionOverridesGlobal(): void
    {
        // Both global and context have --timeout option
        $globalTimeout = OptionBuilder::create()
            ->long('--timeout')
            ->type(\Horde\Argv\Modern\Enum\OptionType::Int)
            ->default(30)
            ->build();

        $contextTimeout = OptionBuilder::create()
            ->long('--timeout')
            ->type(\Horde\Argv\Modern\Enum\OptionType::Int)
            ->default(60)
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->addOption($contextTimeout)
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($globalTimeout)
            ->addContext($deployContext)
            ->build();

        $result = $parser->parse(['deploy', '--timeout', '120']);

        // Context option should be set
        $this->assertSame(120, $result->contextOptions->get('timeout'));
        // Global option should still have default
        $this->assertSame(30, $result->globalOptions->get('timeout'));
    }

    public function testBackwardCompatibilityNoContexts(): void
    {
        $verboseOpt = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->build();

        // Parser with no contexts - should behave like legacy
        $parser = ParserBuilder::create()
            ->addOption($verboseOpt)
            ->build();

        $result = $parser->parse(['--verbose', 'file.txt']);

        // Legacy mode behavior
        $this->assertFalse($result->hasContext());
        $this->assertTrue($result->options->get('verbose'));
        $this->assertSame(['file.txt'], $result->arguments);
    }
}
