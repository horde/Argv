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

use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder, ContextBuilder};
use Horde\Argv\Modern\Help\HelpFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use InvalidArgumentException;

/**
 * Tests for context help generation (Phase 3).
 *
 * @category Horde
 * @package  Argv
 */
#[CoversClass(\Horde\Argv\ImmutableParser::class)]
#[CoversClass(HelpFormatter::class)]
class ContextHelpTest extends TestCase
{
    public function testFormatHelpWithContexts(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application to environment')
            ->build();

        $rollbackContext = ContextBuilder::create('rollback')
            ->withDescription('Rollback to previous version')
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <command>')
            ->withDescription('Application deployment tool')
            ->addContext($deployContext)
            ->addContext($rollbackContext)
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('Usage:', $help);
        $this->assertStringContainsString('Application deployment tool', $help);
        $this->assertStringContainsString('Commands:', $help);
        $this->assertStringContainsString('deploy', $help);
        $this->assertStringContainsString('Deploy application', $help);
        $this->assertStringContainsString('rollback', $help);
        $this->assertStringContainsString('Rollback to previous', $help);
    }

    public function testFormatHelpWithContextAliases(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application')
            ->withAliases(['dep', 'depl'])
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $help = $parser->formatHelp();

        $this->assertStringContainsString('deploy', $help);
        $this->assertStringContainsString('dep', $help);
        $this->assertStringContainsString('depl', $help);
    }

    public function testFormatContextHelp(): void
    {
        $forceOpt = OptionBuilder::create()
            ->long('--force')
            ->flag()
            ->help('Force deployment')
            ->build();

        $timeoutOpt = OptionBuilder::create()
            ->long('--timeout')
            ->type(\Horde\Argv\Modern\Enum\OptionType::Int)
            ->default(60)
            ->help('Deployment timeout in seconds')
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application to environment')
            ->withUsage('%prog [options] <environment>')
            ->withHelp('Deploys the application to the specified environment with health checks and rollback capabilities.')
            ->addOption($forceOpt)
            ->addOption($timeoutOpt)
            ->requiresArguments(1, 'environment name')
            ->build();

        $verboseOpt = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->help('Verbose output')
            ->build();

        $parser = ParserBuilder::create()
            ->withProg('myapp')
            ->addOption($verboseOpt)
            ->addContext($deployContext)
            ->build();

        $help = $parser->formatContextHelp('deploy');

        $this->assertStringContainsString('Usage: myapp deploy', $help);
        $this->assertStringContainsString('Deploy application to environment', $help);
        $this->assertStringContainsString('Deploys the application', $help);
        $this->assertStringContainsString('Context Options:', $help);
        $this->assertStringContainsString('--force', $help);
        $this->assertStringContainsString('--timeout', $help);
        $this->assertStringContainsString('Global Options:', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringContainsString('Arguments:', $help);
        $this->assertStringContainsString('environment name', $help);
    }

    public function testFormatContextHelpInvalidContext(): void
    {
        $parser = ParserBuilder::create()->build();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown context: nonexistent');

        $parser->formatContextHelp('nonexistent');
    }

    public function testGetContexts(): void
    {
        $deployContext = ContextBuilder::create('deploy')->build();
        $rollbackContext = ContextBuilder::create('rollback')->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->addContext($rollbackContext)
            ->build();

        $contexts = $parser->getContexts();

        $this->assertCount(2, $contexts);
        $this->assertSame('deploy', $contexts[0]->name);
        $this->assertSame('rollback', $contexts[1]->name);
    }

    public function testGetContext(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withAliases(['dep'])
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        // By name
        $context = $parser->getContext('deploy');
        $this->assertNotNull($context);
        $this->assertSame('deploy', $context->name);

        // By alias
        $context = $parser->getContext('dep');
        $this->assertNotNull($context);
        $this->assertSame('deploy', $context->name);

        // Not found
        $context = $parser->getContext('nonexistent');
        $this->assertNull($context);
    }

    public function testHasContexts(): void
    {
        $parser1 = ParserBuilder::create()->build();
        $this->assertFalse($parser1->hasContexts());

        $deployContext = ContextBuilder::create('deploy')->build();
        $parser2 = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();
        $this->assertTrue($parser2->hasContexts());
    }

    public function testFormatHelpWithGlobalAndContextOptions(): void
    {
        $globalVerbose = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->help('Verbose output')
            ->build();

        $contextForce = OptionBuilder::create()
            ->long('--force')
            ->flag()
            ->help('Force operation')
            ->build();

        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application')
            ->addOption($contextForce)
            ->build();

        $parser = ParserBuilder::create()
            ->withDescription('Deployment manager')
            ->addOption($globalVerbose)
            ->addContext($deployContext)
            ->build();

        $help = $parser->formatHelp();

        // Should show global options
        $this->assertStringContainsString('--verbose', $help);

        // Context-specific options shown in context help, not main help
        $contextHelp = $parser->formatContextHelp('deploy');
        $this->assertStringContainsString('--force', $contextHelp);
    }

    public function testFormatContextWithArgumentRequirements(): void
    {
        $exactContext = ContextBuilder::create('exact')
            ->requiresArguments(2, 'source destination')
            ->build();

        $rangeContext = ContextBuilder::create('range')
            ->acceptsArguments(1, 3, 'files')
            ->build();

        $atLeastContext = ContextBuilder::create('atleast')
            ->requiresAtLeast(1)
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($exactContext)
            ->addContext($rangeContext)
            ->addContext($atLeastContext)
            ->build();

        // Exact count
        $help1 = $parser->formatContextHelp('exact');
        $this->assertStringContainsString('Requires exactly 2 arguments', $help1);
        $this->assertStringContainsString('source destination', $help1);

        // Range
        $help2 = $parser->formatContextHelp('range');
        $this->assertStringContainsString('Accepts 1-3 arguments', $help2);
        $this->assertStringContainsString('files', $help2);

        // At least
        $help3 = $parser->formatContextHelp('atleast');
        $this->assertStringContainsString('Requires at least 1 argument', $help3);
    }

    public function testBackwardCompatibilityNoContexts(): void
    {
        $verboseOpt = OptionBuilder::create()
            ->long('--verbose')
            ->flag()
            ->help('Verbose output')
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <file>')
            ->withDescription('Process files')
            ->addOption($verboseOpt)
            ->build();

        $help = $parser->formatHelp();

        // Standard help format (no contexts section)
        $this->assertStringContainsString('Usage:', $help);
        $this->assertStringContainsString('Process files', $help);
        $this->assertStringContainsString('--verbose', $help);
        $this->assertStringNotContainsString('Commands:', $help);
    }

    public function testCustomFormatter(): void
    {
        $deployContext = ContextBuilder::create('deploy')
            ->withDescription('Deploy application')
            ->build();

        $parser = ParserBuilder::create()
            ->addContext($deployContext)
            ->build();

        $formatter = HelpFormatter::create()
            ->withWidth(120)
            ->withIndent(4)
            ->build();

        $help = $parser->formatHelp($formatter);

        $this->assertStringContainsString('deploy', $help);
        $this->assertStringContainsString('Deploy application', $help);
    }
}
