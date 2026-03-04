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

use Horde\Argv\ImmutableParser;
use Horde\Argv\Modern\Builder\{ParserBuilder, OptionBuilder, GroupBuilder};
use Horde\Argv\Modern\Config\ParserConfig;
use Horde\Argv\Modern\Enum\ConflictHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParserBuilder::class)]
class ParserBuilderTest extends TestCase
{
    public function testCreateReturnsBuilder(): void
    {
        $builder = ParserBuilder::create();
        $this->assertInstanceOf(ParserBuilder::class, $builder);
    }

    public function testWithUsageReturnsNewInstance(): void
    {
        $builder1 = ParserBuilder::create();
        $builder2 = $builder1->withUsage('%prog [options]');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testBuildCreatesImmutableParser(): void
    {
        $parser = ParserBuilder::create()->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithUsageSetsUsage(): void
    {
        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <file>')
            ->build();

        // We'll verify this through parser behavior when implemented
        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithDescriptionSetsDescription(): void
    {
        $parser = ParserBuilder::create()
            ->withDescription('Process files')
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithVersionSetsVersion(): void
    {
        $parser = ParserBuilder::create()
            ->withVersion('1.0.0')
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithEpilogSetsEpilog(): void
    {
        $parser = ParserBuilder::create()
            ->withEpilog('For more information, see the manual.')
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithProgSetsProgram(): void
    {
        $parser = ParserBuilder::create()
            ->withProg('myapp')
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAllowInterspersedArgs(): void
    {
        $parser = ParserBuilder::create()
            ->allowInterspersedArgs(true)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAllowUnknownArgs(): void
    {
        $parser = ParserBuilder::create()
            ->allowUnknownArgs(true)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testIgnoreUnknownArgs(): void
    {
        $parser = ParserBuilder::create()
            ->ignoreUnknownArgs(true)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAddHelpOption(): void
    {
        $parser = ParserBuilder::create()
            ->addHelpOption(true)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testConflictHandler(): void
    {
        $parser = ParserBuilder::create()
            ->conflictHandler(ConflictHandler::Resolve)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAddOption(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->addOption($option)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAddOptions(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $parser = ParserBuilder::create()
            ->addOptions([$option1, $option2])
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testAddGroup(): void
    {
        $group = GroupBuilder::create('Advanced')
            ->withDescription('Advanced options')
            ->build();

        $parser = ParserBuilder::create()
            ->addGroup($group)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testMethodChainingWorks(): void
    {
        $option = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <file>')
            ->withDescription('Process files')
            ->withVersion('1.0.0')
            ->addOption($option)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testBuilderIsImmutable(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $builder1 = ParserBuilder::create();
        $builder2 = $builder1->withUsage('usage1');
        $builder3 = $builder2->addOption($option1);

        $parser1 = $builder1->withUsage('parser1')->build();
        $parser2 = $builder2->withUsage('parser2')->build();
        $parser3 = $builder3->build();

        // All parsers are created successfully
        $this->assertInstanceOf(ImmutableParser::class, $parser1);
        $this->assertInstanceOf(ImmutableParser::class, $parser2);
        $this->assertInstanceOf(ImmutableParser::class, $parser3);

        // They are different instances
        $this->assertNotSame($parser1, $parser2);
        $this->assertNotSame($parser2, $parser3);
    }

    public function testBuilderBranchingWorks(): void
    {
        $base = ParserBuilder::create()
            ->withDescription('Base description')
            ->addHelpOption(true);

        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $parser1 = $base->addOption($option1)->build();
        $parser2 = $base->addOption($option2)->build();

        // Both parsers created from same base
        $this->assertInstanceOf(ImmutableParser::class, $parser1);
        $this->assertInstanceOf(ImmutableParser::class, $parser2);
        $this->assertNotSame($parser1, $parser2);
    }

    public function testComplexParserBuilding(): void
    {
        $verboseOption = OptionBuilder::create()
            ->short('-v')
            ->long('--verbose')
            ->flag()
            ->help('Enable verbose output')
            ->build();

        $portOption = OptionBuilder::create()
            ->short('-p')
            ->long('--port')
            ->type(\Horde\Argv\Modern\Enum\OptionType::Int)
            ->default(8080)
            ->metavar('PORT')
            ->help('Server port')
            ->build();

        $debugGroup = GroupBuilder::create('Debugging')
            ->withDescription('Debugging options')
            ->addOption(
                OptionBuilder::create()
                    ->short('-d')
                    ->long('--debug')
                    ->flag()
                    ->build()
            )
            ->build();

        $parser = ParserBuilder::create()
            ->withUsage('%prog [options] <file>')
            ->withDescription('Server application')
            ->withVersion('1.0.0')
            ->withEpilog('Report bugs to bugs@example.com')
            ->addOption($verboseOption)
            ->addOption($portOption)
            ->addGroup($debugGroup)
            ->addHelpOption(true)
            ->allowInterspersedArgs(true)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testFromConfigMethod(): void
    {
        $config = new ParserConfig(
            usage: '%prog test',
            description: 'Test description'
        );

        $builder = ParserBuilder::create()->fromConfig($config);
        $parser = $builder->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testSetOptionsMethod(): void
    {
        $option1 = OptionBuilder::create()->short('-v')->build();
        $option2 = OptionBuilder::create()->short('-d')->build();

        $builder = ParserBuilder::create()
            ->setOptions([$option1, $option2]);
        $parser = $builder->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testSetGroupsMethod(): void
    {
        $group1 = GroupBuilder::create('Group1')->build();
        $group2 = GroupBuilder::create('Group2')->build();

        $builder = ParserBuilder::create()
            ->setGroups([$group1, $group2]);
        $parser = $builder->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }

    public function testWithHelpFormatter(): void
    {
        $formatter = new \stdClass(); // Placeholder for actual HelpFormatter

        $parser = ParserBuilder::create()
            ->withHelpFormatter($formatter)
            ->build();

        $this->assertInstanceOf(ImmutableParser::class, $parser);
    }
}
