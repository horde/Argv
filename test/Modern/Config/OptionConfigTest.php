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

use Horde\Argv\Modern\Config\OptionConfig;
use Horde\Argv\Modern\Enum\{OptionAction, OptionType};
use Horde\Argv\Modern\Exception\InvalidOptionException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(OptionConfig::class)]
class OptionConfigTest extends TestCase
{
    public function testConstructWithMinimalOptions(): void
    {
        $config = new OptionConfig(short: '-v');

        $this->assertSame('-v', $config->short);
        $this->assertSame('', $config->long);
        $this->assertSame(OptionAction::Store, $config->action);
        $this->assertSame(OptionType::String, $config->type);
    }

    public function testConstructWithAllOptions(): void
    {
        $validator = fn($v) => true;
        $map = fn($v) => strtolower($v);

        $config = new OptionConfig(
            short: '-v',
            long: '--verbose',
            action: OptionAction::StoreTrue,
            type: OptionType::Int,
            dest: 'verbosity',
            default: 0,
            help: 'Increase verbosity',
            validator: $validator,
            map: $map
        );

        $this->assertSame('-v', $config->short);
        $this->assertSame('--verbose', $config->long);
        $this->assertSame(OptionAction::StoreTrue, $config->action);
        $this->assertSame(OptionType::Int, $config->type);
        $this->assertSame('verbosity', $config->dest);
        $this->assertSame(0, $config->default);
        $this->assertSame('Increase verbosity', $config->help);
        $this->assertSame($validator, $config->validator);
        $this->assertSame($map, $config->map);
    }

    public function testThrowsExceptionWhenNoShortOrLong(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('must have either short or long name');
        new OptionConfig();
    }

    public function testThrowsExceptionForInvalidShortFormat(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Short option must be format: -X');
        new OptionConfig(short: 'v');
    }

    public function testThrowsExceptionForInvalidLongFormat(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Long option must be format: --name');
        new OptionConfig(long: 'verbose');
    }

    public function testThrowsExceptionForChoicesWithNonStoreAction(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Choices only valid for Store and Append');
        new OptionConfig(
            short: '-v',
            action: OptionAction::StoreTrue,
            choices: ['a', 'b']
        );
    }

    public function testThrowsExceptionForStoreConstWithoutConst(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('StoreConst/AppendConst actions require const value');
        new OptionConfig(
            short: '-v',
            action: OptionAction::StoreConst
        );
    }

    public function testThrowsExceptionForCallbackWithoutCallable(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Callback action requires callback function');
        new OptionConfig(
            short: '-v',
            action: OptionAction::Callback
        );
    }

    public function testGetDestinationUsesExplicitDest(): void
    {
        $config = new OptionConfig(short: '-v', dest: 'custom');
        $this->assertSame('custom', $config->getDestination());
    }

    public function testGetDestinationGeneratesFromLongOption(): void
    {
        $config = new OptionConfig(long: '--verbose');
        $this->assertSame('verbose', $config->getDestination());
    }

    public function testGetDestinationGeneratesFromShortOption(): void
    {
        $config = new OptionConfig(short: '-v');
        $this->assertSame('v', $config->getDestination());
    }

    public function testGetDestinationPrefersLongOverShort(): void
    {
        $config = new OptionConfig(short: '-v', long: '--verbose');
        $this->assertSame('verbose', $config->getDestination());
    }

    public function testGetDisplayNamePrefersLongOption(): void
    {
        $config = new OptionConfig(short: '-v', long: '--verbose');
        $this->assertSame('--verbose', $config->getDisplayName());
    }

    public function testGetDisplayNameUsesShortIfNoLong(): void
    {
        $config = new OptionConfig(short: '-v');
        $this->assertSame('-v', $config->getDisplayName());
    }

    public function testAcceptsChoicesForStoreAction(): void
    {
        $config = new OptionConfig(
            short: '-f',
            action: OptionAction::Store,
            choices: ['json', 'xml']
        );
        $this->assertSame(['json', 'xml'], $config->choices);
    }

    public function testAcceptsChoicesForAppendAction(): void
    {
        $config = new OptionConfig(
            short: '-r',
            action: OptionAction::Append,
            choices: ['admin', 'user']
        );
        $this->assertSame(['admin', 'user'], $config->choices);
    }

    public function testValidShortOptionsAccepted(): void
    {
        new OptionConfig(short: '-a');
        new OptionConfig(short: '-Z');
        new OptionConfig(short: '-0');
        new OptionConfig(short: '-9');
        $this->assertTrue(true); // No exception
    }

    public function testValidLongOptionsAccepted(): void
    {
        new OptionConfig(long: '--verbose');
        new OptionConfig(long: '--long-option');
        new OptionConfig(long: '--with-hyphens');
        new OptionConfig(long: '--a1b2c3');
        $this->assertTrue(true); // No exception
    }

    public function testConfigIsReadonly(): void
    {
        $config = new OptionConfig(short: '-v');

        $reflection = new ReflectionClass($config);
        $this->assertTrue($reflection->isReadOnly());
    }
}
