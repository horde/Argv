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

namespace Horde\Argv\Test\Modern\Enum;

use Horde\Argv\Modern\Enum\OptionType;
use Horde\Argv\Modern\Exception\ValueValidationException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OptionType::class)]
class OptionTypeTest extends TestCase
{
    public function testIntTypeValidatesInteger(): void
    {
        $this->assertTrue(OptionType::Int->validate('42'));
        $this->assertTrue(OptionType::Int->validate(42));
    }

    public function testIntTypeRejectsFloat(): void
    {
        $this->assertFalse(OptionType::Int->validate('3.14'));
    }

    public function testIntTypeRejectsNonNumeric(): void
    {
        $this->assertFalse(OptionType::Int->validate('abc'));
    }

    public function testFloatTypeValidatesFloat(): void
    {
        $this->assertTrue(OptionType::Float->validate('3.14'));
        $this->assertTrue(OptionType::Float->validate(3.14));
    }

    public function testFloatTypeValidatesInteger(): void
    {
        $this->assertTrue(OptionType::Float->validate('42'));
        $this->assertTrue(OptionType::Float->validate(42));
    }

    public function testFloatTypeRejectsNonNumeric(): void
    {
        $this->assertFalse(OptionType::Float->validate('abc'));
    }

    public function testStringTypeAcceptsAnything(): void
    {
        $this->assertTrue(OptionType::String->validate('anything'));
        $this->assertTrue(OptionType::String->validate('123'));
        $this->assertTrue(OptionType::String->validate(''));
    }

    public function testIntTypeConvertsToInt(): void
    {
        $result = OptionType::Int->convert('42');
        $this->assertIsInt($result);
        $this->assertSame(42, $result);
    }

    public function testIntTypeThrowsOnInvalidValue(): void
    {
        $this->expectException(ValueValidationException::class);
        $this->expectExceptionMessage('not a valid integer');
        OptionType::Int->convert('abc');
    }

    public function testFloatTypeConvertsToFloat(): void
    {
        $result = OptionType::Float->convert('3.14');
        $this->assertIsFloat($result);
        $this->assertSame(3.14, $result);
    }

    public function testFloatTypeThrowsOnInvalidValue(): void
    {
        $this->expectException(ValueValidationException::class);
        $this->expectExceptionMessage('not a valid floating-point number');
        OptionType::Float->convert('abc');
    }

    public function testStringTypeConvertsToString(): void
    {
        $result = OptionType::String->convert(123);
        $this->assertIsString($result);
        $this->assertSame('123', $result);
    }

    public function testIntTypeGetPhpType(): void
    {
        $this->assertSame('int', OptionType::Int->getPhpType());
    }

    public function testFloatTypeGetPhpType(): void
    {
        $this->assertSame('float', OptionType::Float->getPhpType());
    }

    public function testStringTypeGetPhpType(): void
    {
        $this->assertSame('string', OptionType::String->getPhpType());
    }

    public function testIntTypeGetDescription(): void
    {
        $this->assertSame('integer', OptionType::Int->getDescription());
    }

    public function testFloatTypeGetDescription(): void
    {
        $this->assertSame('floating-point number', OptionType::Float->getDescription());
    }

    public function testStringTypeGetDescription(): void
    {
        $this->assertSame('string', OptionType::String->getDescription());
    }

    public function testValueValidationExceptionContainsContext(): void
    {
        try {
            OptionType::Int->convert('invalid');
            $this->fail('Expected exception not thrown');
        } catch (ValueValidationException $e) {
            $this->assertSame('', $e->getOptionName()); // Set by caller
            $this->assertSame('invalid', $e->getProvidedValue());
            $this->assertSame(OptionType::Int, $e->getExpectedType());

            $context = $e->getContext();
            $this->assertArrayHasKey('provided', $context);
            $this->assertArrayHasKey('expected_type', $context);
        }
    }
}
