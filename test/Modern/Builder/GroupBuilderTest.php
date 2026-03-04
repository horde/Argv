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

use Horde\Argv\Modern\Builder\{GroupBuilder, OptionBuilder};
use Horde\Argv\Modern\Config\OptionGroupConfig;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GroupBuilder::class)]
class GroupBuilderTest extends TestCase
{
    public function testCreateReturnsBuilder(): void
    {
        $builder = GroupBuilder::create('Test Group');
        $this->assertInstanceOf(GroupBuilder::class, $builder);
    }

    public function testWithDescriptionReturnsNewInstance(): void
    {
        $builder1 = GroupBuilder::create('Test');
        $builder2 = $builder1->withDescription('Description');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testAddOptionReturnsNewInstance(): void
    {
        $option = OptionBuilder::create()->short('-v')->build();
        $builder1 = GroupBuilder::create('Test');
        $builder2 = $builder1->addOption($option);

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildCreatesGroupConfig(): void
    {
        $config = GroupBuilder::create('Test Group')->build();

        $this->assertInstanceOf(OptionGroupConfig::class, $config);
    }

    public function testGroupHasCorrectTitle(): void
    {
        $config = GroupBuilder::create('Advanced Options')->build();

        $this->assertSame('Advanced Options', $config->title);
    }

    public function testGroupHasDescription(): void
    {
        $config = GroupBuilder::create('Test')
            ->withDescription('Test description')
            ->build();

        $this->assertSame('Test description', $config->description);
    }

    public function testGroupHasDefaultEmptyDescription(): void
    {
        $config = GroupBuilder::create('Test')->build();

        $this->assertSame('', $config->description);
    }

    public function testAddOptionAddsToGroup(): void
    {
        $option = OptionBuilder::create()->short('-v')->build();
        $config = GroupBuilder::create('Test')
            ->addOption($option)
            ->build();

        $this->assertCount(1, $config->options);
        $this->assertSame($option, $config->options[0]);
    }

    public function testAddOptionsAddsMultipleOptions(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();
        $option3 = OptionBuilder::create()->short('-t')->build();

        $config = GroupBuilder::create('Test')
            ->addOptions([$option1, $option2, $option3])
            ->build();

        $this->assertCount(3, $config->options);
        $this->assertSame($option1, $config->options[0]);
        $this->assertSame($option2, $config->options[1]);
        $this->assertSame($option3, $config->options[2]);
    }

    public function testMethodChainingWorks(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $config = GroupBuilder::create('Advanced')
            ->withDescription('Advanced options for power users')
            ->addOption($option1)
            ->addOption($option2)
            ->build();

        $this->assertSame('Advanced', $config->title);
        $this->assertSame('Advanced options for power users', $config->description);
        $this->assertCount(2, $config->options);
    }

    public function testBuilderIsImmutable(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $builder1 = GroupBuilder::create('Test');
        $builder2 = $builder1->addOption($option1);
        $builder3 = $builder2->addOption($option2);

        $config1 = $builder1->build();
        $config2 = $builder2->build();
        $config3 = $builder3->build();

        $this->assertCount(0, $config1->options);
        $this->assertCount(1, $config2->options);
        $this->assertCount(2, $config3->options);
    }

    public function testBuilderBranchingWorks(): void
    {
        $base = GroupBuilder::create('Base Group')
            ->withDescription('Base description');

        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $config1 = $base->addOption($option1)->build();
        $config2 = $base->addOption($option2)->build();

        // Both configs share the base description
        $this->assertSame('Base description', $config1->description);
        $this->assertSame('Base description', $config2->description);

        // But have different options
        $this->assertCount(1, $config1->options);
        $this->assertCount(1, $config2->options);
        $this->assertSame('-v', $config1->options[0]->short);
        $this->assertSame('-d', $config2->options[0]->short);
    }

    public function testComplexGroupBuilding(): void
    {
        $debugOption = OptionBuilder::create()
            ->short('-d')
            ->long('--debug')
            ->flag()
            ->help('Enable debug mode')
            ->build();

        $traceOption = OptionBuilder::create()
            ->short('-t')
            ->long('--trace')
            ->counter()
            ->help('Enable trace output')
            ->build();

        $logOption = OptionBuilder::create()
            ->short('-l')
            ->long('--log-file')
            ->metavar('FILE')
            ->help('Log file path')
            ->build();

        $config = GroupBuilder::create('Debugging Options')
            ->withDescription('Options for debugging and troubleshooting')
            ->addOption($debugOption)
            ->addOption($traceOption)
            ->addOption($logOption)
            ->build();

        $this->assertSame('Debugging Options', $config->title);
        $this->assertSame('Options for debugging and troubleshooting', $config->description);
        $this->assertCount(3, $config->options);
        $this->assertSame('--debug', $config->options[0]->long);
        $this->assertSame('--trace', $config->options[1]->long);
        $this->assertSame('--log-file', $config->options[2]->long);
    }
}
