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

namespace Horde\Argv\Test\Modern\Config;

use Horde\Argv\Modern\Config\{ContextConfig, OptionConfig};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use InvalidArgumentException;

/**
 * Tests for ContextConfig.
 *
 * @category Horde
 * @package  Argv
 */
#[CoversClass(ContextConfig::class)]
class ContextConfigTest extends TestCase
{
    public function testBasicConstruction(): void
    {
        $context = new ContextConfig(name: 'deploy');

        $this->assertSame('deploy', $context->name);
        $this->assertSame('', $context->description);
        $this->assertSame([], $context->aliases);
        $this->assertSame([], $context->options);
        $this->assertSame(0, $context->minArgs);
        $this->assertSame(PHP_INT_MAX, $context->maxArgs);
    }

    public function testFullConstruction(): void
    {
        $option = new OptionConfig(long: '--force');

        $context = new ContextConfig(
            name: 'deploy',
            description: 'Deploy application',
            usage: '%prog deploy [options] <env>',
            help: 'Deploys the application to an environment',
            aliases: ['dep', 'depl'],
            options: [$option],
            minArgs: 1,
            maxArgs: 2,
            argsDescription: 'environment [version]'
        );

        $this->assertSame('deploy', $context->name);
        $this->assertSame('Deploy application', $context->description);
        $this->assertSame('%prog deploy [options] <env>', $context->usage);
        $this->assertSame('Deploys the application to an environment', $context->help);
        $this->assertSame(['dep', 'depl'], $context->aliases);
        $this->assertCount(1, $context->options);
        $this->assertSame(1, $context->minArgs);
        $this->assertSame(2, $context->maxArgs);
        $this->assertSame('environment [version]', $context->argsDescription);
    }

    public function testEmptyNameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context name cannot be empty');

        new ContextConfig(name: '');
    }

    public function testNegativeMinArgsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minArgs must be >= 0');

        new ContextConfig(name: 'test', minArgs: -1);
    }

    public function testMaxArgsLessThanMinArgsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maxArgs must be >= minArgs');

        new ContextConfig(name: 'test', minArgs: 5, maxArgs: 3);
    }

    public function testMatchesName(): void
    {
        $context = new ContextConfig(name: 'deploy', aliases: ['dep']);

        $this->assertTrue($context->matches('deploy'));
        $this->assertTrue($context->matches('dep'));
        $this->assertFalse($context->matches('rollback'));
        $this->assertFalse($context->matches(''));
    }

    public function testGetAllNames(): void
    {
        $context = new ContextConfig(name: 'deploy', aliases: ['dep', 'depl']);

        $names = $context->getAllNames();

        $this->assertSame(['deploy', 'dep', 'depl'], $names);
    }

    public function testValidateArgumentCountExact(): void
    {
        $context = new ContextConfig(name: 'test', minArgs: 2, maxArgs: 2);

        $this->assertFalse($context->validateArgumentCount(1));
        $this->assertTrue($context->validateArgumentCount(2));
        $this->assertFalse($context->validateArgumentCount(3));
    }

    public function testValidateArgumentCountRange(): void
    {
        $context = new ContextConfig(name: 'test', minArgs: 1, maxArgs: 3);

        $this->assertFalse($context->validateArgumentCount(0));
        $this->assertTrue($context->validateArgumentCount(1));
        $this->assertTrue($context->validateArgumentCount(2));
        $this->assertTrue($context->validateArgumentCount(3));
        $this->assertFalse($context->validateArgumentCount(4));
    }

    public function testGetArgumentCountErrorExact(): void
    {
        $context = new ContextConfig(name: 'deploy', minArgs: 1, maxArgs: 1);

        $error = $context->getArgumentCountError(0);
        $this->assertStringContainsString('requires exactly 1 argument', $error);
        $this->assertStringContainsString('got 0', $error);

        $error = $context->getArgumentCountError(2);
        $this->assertStringContainsString('requires exactly 1 argument', $error);
        $this->assertStringContainsString('got 2', $error);
    }

    public function testGetArgumentCountErrorPlural(): void
    {
        $context = new ContextConfig(name: 'test', minArgs: 2, maxArgs: 2);

        $error = $context->getArgumentCountError(1);
        $this->assertStringContainsString('requires exactly 2 arguments', $error);
    }

    public function testGetArgumentCountErrorTooFew(): void
    {
        $context = new ContextConfig(name: 'deploy', minArgs: 2, maxArgs: 5);

        $error = $context->getArgumentCountError(1);
        $this->assertStringContainsString('requires at least 2 arguments', $error);
        $this->assertStringContainsString('got 1', $error);
    }

    public function testGetArgumentCountErrorTooMany(): void
    {
        $context = new ContextConfig(name: 'deploy', minArgs: 1, maxArgs: 3);

        $error = $context->getArgumentCountError(5);
        $this->assertStringContainsString('accepts at most 3 arguments', $error);
        $this->assertStringContainsString('got 5', $error);
    }

    public function testHasSubContexts(): void
    {
        $context1 = new ContextConfig(name: 'test');
        $this->assertFalse($context1->hasSubContexts());

        $subContext = new ContextConfig(name: 'sub');
        $context2 = new ContextConfig(name: 'test', subContexts: [$subContext]);
        $this->assertTrue($context2->hasSubContexts());
    }

    public function testFindSubContext(): void
    {
        $sub1 = new ContextConfig(name: 'start');
        $sub2 = new ContextConfig(name: 'stop', aliases: ['halt']);

        $context = new ContextConfig(name: 'service', subContexts: [$sub1, $sub2]);

        $found = $context->findSubContext('start');
        $this->assertSame('start', $found->name);

        $found = $context->findSubContext('stop');
        $this->assertSame('stop', $found->name);

        $found = $context->findSubContext('halt');
        $this->assertSame('stop', $found->name);

        $found = $context->findSubContext('restart');
        $this->assertNull($found);
    }

    public function testNameCannotBeInAliases(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context name cannot be in aliases list');

        new ContextConfig(name: 'deploy', aliases: ['deploy', 'dep']);
    }
}
