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

namespace Horde\Argv\Test\Modern\Builder;

use Horde\Argv\Modern\Builder\{ContextBuilder, OptionBuilder};
use Horde\Argv\Modern\Config\ContextConfig;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for ContextBuilder.
 *
 * @category Horde
 * @package  Argv
 */
#[CoversClass(ContextBuilder::class)]
class ContextBuilderTest extends TestCase
{
    public function testCreateBasic(): void
    {
        $context = ContextBuilder::create('deploy')->build();

        $this->assertInstanceOf(ContextConfig::class, $context);
        $this->assertSame('deploy', $context->name);
    }

    public function testImmutability(): void
    {
        $builder1 = ContextBuilder::create('deploy');
        $builder2 = $builder1->withDescription('Deploy app');

        $this->assertNotSame($builder1, $builder2);

        $context1 = $builder1->build();
        $context2 = $builder2->build();

        $this->assertSame('', $context1->description);
        $this->assertSame('Deploy app', $context2->description);
    }

    public function testWithDescription(): void
    {
        $context = ContextBuilder::create('deploy')
            ->withDescription('Deploy application to environment')
            ->build();

        $this->assertSame('Deploy application to environment', $context->description);
    }

    public function testWithUsage(): void
    {
        $context = ContextBuilder::create('deploy')
            ->withUsage('%prog deploy [options] <env>')
            ->build();

        $this->assertSame('%prog deploy [options] <env>', $context->usage);
    }

    public function testWithHelp(): void
    {
        $context = ContextBuilder::create('deploy')
            ->withHelp('Detailed help text')
            ->build();

        $this->assertSame('Detailed help text', $context->help);
    }

    public function testWithAliases(): void
    {
        $context = ContextBuilder::create('deploy')
            ->withAliases(['dep', 'depl'])
            ->build();

        $this->assertSame(['dep', 'depl'], $context->aliases);
    }

    public function testAddOption(): void
    {
        $option = OptionBuilder::create()->long('--force')->flag()->build();

        $context = ContextBuilder::create('deploy')
            ->addOption($option)
            ->build();

        $this->assertCount(1, $context->options);
        $this->assertSame($option, $context->options[0]);
    }

    public function testAddMultipleOptions(): void
    {
        $opt1 = OptionBuilder::create()->long('--force')->flag()->build();
        $opt2 = OptionBuilder::create()->long('--timeout')->build();

        $context = ContextBuilder::create('deploy')
            ->addOption($opt1)
            ->addOption($opt2)
            ->build();

        $this->assertCount(2, $context->options);
    }

    public function testAddOptions(): void
    {
        $opt1 = OptionBuilder::create()->long('--force')->flag()->build();
        $opt2 = OptionBuilder::create()->long('--timeout')->build();

        $context = ContextBuilder::create('deploy')
            ->addOptions([$opt1, $opt2])
            ->build();

        $this->assertCount(2, $context->options);
    }

    public function testRequiresArguments(): void
    {
        $context = ContextBuilder::create('deploy')
            ->requiresArguments(2, 'environment version')
            ->build();

        $this->assertSame(2, $context->minArgs);
        $this->assertSame(2, $context->maxArgs);
        $this->assertSame('environment version', $context->argsDescription);
    }

    public function testAcceptsArguments(): void
    {
        $context = ContextBuilder::create('deploy')
            ->acceptsArguments(1, 3, 'files')
            ->build();

        $this->assertSame(1, $context->minArgs);
        $this->assertSame(3, $context->maxArgs);
        $this->assertSame('files', $context->argsDescription);
    }

    public function testAcceptsArgumentsUnlimited(): void
    {
        $context = ContextBuilder::create('process')
            ->acceptsArguments(0, description: 'files')
            ->build();

        $this->assertSame(0, $context->minArgs);
        $this->assertSame(PHP_INT_MAX, $context->maxArgs);
    }

    public function testRequiresExactly(): void
    {
        $context = ContextBuilder::create('deploy')
            ->requiresExactly(1)
            ->build();

        $this->assertSame(1, $context->minArgs);
        $this->assertSame(1, $context->maxArgs);
    }

    public function testRequiresAtLeast(): void
    {
        $context = ContextBuilder::create('deploy')
            ->requiresAtLeast(2)
            ->build();

        $this->assertSame(2, $context->minArgs);
        $this->assertSame(PHP_INT_MAX, $context->maxArgs);
    }

    public function testRequiresAtMost(): void
    {
        $context = ContextBuilder::create('deploy')
            ->requiresAtMost(5)
            ->build();

        $this->assertSame(0, $context->minArgs);
        $this->assertSame(5, $context->maxArgs);
    }

    public function testAddContext(): void
    {
        $subContext = ContextBuilder::create('start')->build();

        $context = ContextBuilder::create('service')
            ->addContext($subContext)
            ->build();

        $this->assertCount(1, $context->subContexts);
        $this->assertSame($subContext, $context->subContexts[0]);
    }

    public function testFluentInterface(): void
    {
        $option = OptionBuilder::create()->long('--force')->flag()->build();

        $context = ContextBuilder::create('deploy')
            ->withDescription('Deploy application')
            ->withUsage('%prog deploy <env>')
            ->withAliases(['dep'])
            ->addOption($option)
            ->requiresArguments(1, 'environment')
            ->build();

        $this->assertSame('deploy', $context->name);
        $this->assertSame('Deploy application', $context->description);
        $this->assertSame('%prog deploy <env>', $context->usage);
        $this->assertSame(['dep'], $context->aliases);
        $this->assertCount(1, $context->options);
        $this->assertSame(1, $context->minArgs);
        $this->assertSame(1, $context->maxArgs);
        $this->assertSame('environment', $context->argsDescription);
    }

    public function testNestedContexts(): void
    {
        $startContext = ContextBuilder::create('start')
            ->withDescription('Start service')
            ->build();

        $stopContext = ContextBuilder::create('stop')
            ->withDescription('Stop service')
            ->build();

        $serviceContext = ContextBuilder::create('service')
            ->withDescription('Manage services')
            ->addContext($startContext)
            ->addContext($stopContext)
            ->build();

        $this->assertCount(2, $serviceContext->subContexts);
        $this->assertSame('start', $serviceContext->subContexts[0]->name);
        $this->assertSame('stop', $serviceContext->subContexts[1]->name);
    }
}
