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

use Horde\Argv\Modern\Result\OptionValues;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(OptionValues::class)]
class OptionValuesTest extends TestCase
{
    public function testGetReturnsValue(): void
    {
        $values = new OptionValues(['verbose' => true, 'port' => 8080]);

        $this->assertTrue($values->get('verbose'));
        $this->assertSame(8080, $values->get('port'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $values = new OptionValues(['verbose' => true]);

        $this->assertNull($values->get('missing'));
        $this->assertSame('default', $values->get('missing', 'default'));
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $values = new OptionValues(['verbose' => true]);

        $this->assertTrue($values->has('verbose'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        $values = new OptionValues(['verbose' => true]);

        $this->assertFalse($values->has('missing'));
    }

    public function testHasReturnsTrueEvenIfValueIsNull(): void
    {
        $values = new OptionValues(['explicit-null' => null]);

        $this->assertTrue($values->has('explicit-null'));
        $this->assertNull($values->get('explicit-null'));
    }

    public function testAllReturnsAllValues(): void
    {
        $data = ['verbose' => true, 'port' => 8080];
        $values = new OptionValues($data);

        $this->assertSame($data, $values->all());
    }

    public function testKeysReturnsAllKeys(): void
    {
        $values = new OptionValues(['verbose' => true, 'port' => 8080]);

        $this->assertSame(['verbose', 'port'], $values->keys());
    }

    public function testIsEmptyReturnsTrueForEmptyValues(): void
    {
        $values = new OptionValues([]);

        $this->assertTrue($values->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyValues(): void
    {
        $values = new OptionValues(['verbose' => true]);

        $this->assertFalse($values->isEmpty());
    }

    public function testCountReturnsNumberOfValues(): void
    {
        $values = new OptionValues(['verbose' => true, 'port' => 8080]);

        $this->assertSame(2, $values->count());
    }

    public function testCountReturnsZeroForEmpty(): void
    {
        $values = new OptionValues([]);

        $this->assertSame(0, $values->count());
    }

    public function testIsReadonly(): void
    {
        $values = new OptionValues(['test' => 'value']);

        $reflection = new ReflectionClass($values);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testConstructWithEmptyArray(): void
    {
        $values = new OptionValues();

        $this->assertTrue($values->isEmpty());
        $this->assertSame([], $values->all());
    }
}
