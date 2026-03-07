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

use Horde\Argv\Modern\Builder\OptionBuilder;
use Horde\Argv\Modern\Config\OptionConfig;
use Horde\Argv\Modern\Enum\{OptionAction, OptionType};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OptionBuilder::class)]
class OptionBuilderTest extends TestCase
{
    public function testCreateReturnsBuilder(): void
    {
        $builder = OptionBuilder::create();
        $this->assertInstanceOf(OptionBuilder::class, $builder);
    }

    public function testShortMethodReturnsNewInstance(): void
    {
        $builder1 = OptionBuilder::create();
        $builder2 = $builder1->short('-v');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildCreatesOptionConfig(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->build();

        $this->assertInstanceOf(OptionConfig::class, $config);
        $this->assertSame('-v', $config->short);
    }

    public function testMethodChainingWorks(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->help('Enable verbose output')
            ->build();

        $this->assertSame('-v', $config->short);
        $this->assertSame('--verbose', $config->long);
        $this->assertSame('Enable verbose output', $config->help);
    }

    public function testActionMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->action(OptionAction::StoreTrue)
            ->build();

        $this->assertSame(OptionAction::StoreTrue, $config->action);
    }

    public function testTypeMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-p')
            ->type(OptionType::Int)
            ->build();

        $this->assertSame(OptionType::Int, $config->type);
    }

    public function testDestMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->dest('verbosity')
            ->build();

        $this->assertSame('verbosity', $config->dest);
    }

    public function testDefaultMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-p')
            ->default(8080)
            ->build();

        $this->assertSame(8080, $config->default);
    }

    public function testConstMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->action(OptionAction::StoreConst)
            ->const('verbose')
            ->build();

        $this->assertSame('verbose', $config->const);
    }

    public function testMetavarMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-f')
            ->metavar('FILE')
            ->build();

        $this->assertSame('FILE', $config->metavar);
    }

    public function testChoicesMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-f')
            ->choices(['json', 'xml'])
            ->build();

        $this->assertSame(['json', 'xml'], $config->choices);
    }

    public function testNargsMethod(): void
    {
        $config = OptionBuilder::create()
            ->short('-f')
            ->nargs(2)
            ->build();

        $this->assertSame(2, $config->nargs);
    }

    public function testCallbackMethod(): void
    {
        $callback = fn($v) => $v;
        $config = OptionBuilder::create()
            ->short('-c')
            ->action(OptionAction::Callback)
            ->callback($callback)
            ->build();

        $this->assertSame($callback, $config->callback);
    }

    public function testValidatorMethod(): void
    {
        $validator = fn($v) => true;
        $config = OptionBuilder::create()
            ->short('-p')
            ->validator($validator)
            ->build();

        $this->assertSame($validator, $config->validator);
    }

    public function testMapMethod(): void
    {
        $map = fn($v) => strtolower($v);
        $config = OptionBuilder::create()
            ->short('-e')
            ->map($map)
            ->build();

        $this->assertSame($map, $config->map);
    }

    public function testFlagConvenience(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $this->assertSame(OptionAction::StoreTrue, $config->action);
    }

    public function testRepeatableConvenience(): void
    {
        $config = OptionBuilder::create()
            ->short('-r')
            ->repeatable()
            ->build();

        $this->assertSame(OptionAction::Append, $config->action);
    }

    public function testCounterConvenience(): void
    {
        $config = OptionBuilder::create()
            ->short('-v')
            ->counter()
            ->build();

        $this->assertSame(OptionAction::Count, $config->action);
    }

    public function testBuilderIsImmutable(): void
    {
        $builder1 = OptionBuilder::create();
        $builder2 = $builder1->short('-v');
        $builder3 = $builder2->long('--verbose');

        $config1 = $builder1->long('--test1')->build();
        $config2 = $builder2->long('--test2')->build();
        $config3 = $builder3->build();

        $this->assertSame('--test1', $config1->long);
        $this->assertSame('--test2', $config2->long);
        $this->assertSame('--verbose', $config3->long);
    }

    public function testComplexOptionBuilding(): void
    {
        $validator = fn($v) => $v > 0 && $v < 65536;
        $map = fn($v) => (int) $v;

        $config = OptionBuilder::create()
            ->short('-p')
            ->long('--port')
            ->type(OptionType::Int)
            ->default(8080)
            ->help('Server port number')
            ->metavar('PORT')
            ->validator($validator)
            ->map($map)
            ->build();

        $this->assertSame('-p', $config->short);
        $this->assertSame('--port', $config->long);
        $this->assertSame(OptionType::Int, $config->type);
        $this->assertSame(8080, $config->default);
        $this->assertSame('Server port number', $config->help);
        $this->assertSame('PORT', $config->metavar);
        $this->assertSame($validator, $config->validator);
        $this->assertSame($map, $config->map);
    }

    public function testBuilderBranchingWorks(): void
    {
        $base = OptionBuilder::create()
            ->short('-v')
            ->help('Base help');

        $config1 = $base->long('--verbose')->build();
        $config2 = $base->long('--version')->build();

        $this->assertSame('--verbose', $config1->long);
        $this->assertSame('--version', $config2->long);
        $this->assertSame('Base help', $config1->help);
        $this->assertSame('Base help', $config2->help);
    }
}
