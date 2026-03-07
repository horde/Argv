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
use ReflectionClass;

#[CoversClass(ParseResult::class)]
class ParseResultTest extends TestCase
{
    public function testConstructWithOptions(): void
    {
        $options = new OptionValues(['verbose' => true]);
        $arguments = ['file.txt'];

        $result = new ParseResult($options, $arguments);

        $this->assertSame($options, $result->options);
        $this->assertSame($arguments, $result->arguments);
        $this->assertSame([], $result->unknown);
    }

    public function testConstructWithUnknown(): void
    {
        $options = new OptionValues([]);
        $arguments = ['file.txt'];
        $unknown = ['--unknown'];

        $result = new ParseResult($options, $arguments, $unknown);

        $this->assertSame($unknown, $result->unknown);
    }

    public function testHasUnknownReturnsTrueWhenUnknownPresent(): void
    {
        $result = new ParseResult(
            new OptionValues([]),
            [],
            ['--unknown']
        );

        $this->assertTrue($result->hasUnknown());
    }

    public function testHasUnknownReturnsFalseWhenNoUnknown(): void
    {
        $result = new ParseResult(
            new OptionValues([]),
            []
        );

        $this->assertFalse($result->hasUnknown());
    }

    public function testHasArgumentsReturnsTrueWhenArgumentsPresent(): void
    {
        $result = new ParseResult(
            new OptionValues([]),
            ['file.txt']
        );

        $this->assertTrue($result->hasArguments());
    }

    public function testHasArgumentsReturnsFalseWhenNoArguments(): void
    {
        $result = new ParseResult(
            new OptionValues([]),
            []
        );

        $this->assertFalse($result->hasArguments());
    }

    public function testGetOptionShorthand(): void
    {
        $options = new OptionValues(['verbose' => true, 'port' => 8080]);
        $result = new ParseResult($options, []);

        $this->assertTrue($result->getOption('verbose'));
        $this->assertSame(8080, $result->getOption('port'));
    }

    public function testGetOptionWithDefault(): void
    {
        $result = new ParseResult(new OptionValues([]), []);

        $this->assertNull($result->getOption('missing'));
        $this->assertSame('default', $result->getOption('missing', 'default'));
    }

    public function testIsReadonly(): void
    {
        $result = new ParseResult(new OptionValues([]), []);

        $reflection = new ReflectionClass($result);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testSeparatesOptionsArgumentsAndUnknown(): void
    {
        $options = new OptionValues(['verbose' => true]);
        $arguments = ['file1.txt', 'file2.txt'];
        $unknown = ['--unknown-flag', '--another'];

        $result = new ParseResult($options, $arguments, $unknown);

        $this->assertTrue($result->options->get('verbose'));
        $this->assertCount(2, $result->arguments);
        $this->assertCount(2, $result->unknown);
        $this->assertSame('file1.txt', $result->arguments[0]);
        $this->assertSame('--unknown-flag', $result->unknown[0]);
    }
}
