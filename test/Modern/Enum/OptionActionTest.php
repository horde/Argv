<?php

declare(strict_types=1);

/**
 * Copyright 2024-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ralf Lang <ralf.lang@ralf-lang.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Argv
 */

namespace Horde\Argv\Test\Modern\Enum;

use Horde\Argv\Modern\Enum\OptionAction;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OptionAction::class)]
class OptionActionTest extends TestCase
{
    public function testStoreActionTakesValue(): void
    {
        $this->assertTrue(OptionAction::Store->takesValue());
    }

    public function testStoreTrueActionDoesNotTakeValue(): void
    {
        $this->assertFalse(OptionAction::StoreTrue->takesValue());
    }

    public function testStoreFalseActionDoesNotTakeValue(): void
    {
        $this->assertFalse(OptionAction::StoreFalse->takesValue());
    }

    public function testAppendActionTakesValue(): void
    {
        $this->assertTrue(OptionAction::Append->takesValue());
    }

    public function testCountActionDoesNotTakeValue(): void
    {
        $this->assertFalse(OptionAction::Count->takesValue());
    }

    public function testCallbackActionTakesValue(): void
    {
        $this->assertTrue(OptionAction::Callback->takesValue());
    }

    public function testStoreActionRequiresArgument(): void
    {
        $this->assertTrue(OptionAction::Store->requiresArgument());
    }

    public function testAppendActionRequiresArgument(): void
    {
        $this->assertTrue(OptionAction::Append->requiresArgument());
    }

    public function testStoreTrueActionDoesNotRequireArgument(): void
    {
        $this->assertFalse(OptionAction::StoreTrue->requiresArgument());
    }

    public function testStoreTrueActionIsBoolean(): void
    {
        $this->assertTrue(OptionAction::StoreTrue->isBoolean());
    }

    public function testStoreFalseActionIsBoolean(): void
    {
        $this->assertTrue(OptionAction::StoreFalse->isBoolean());
    }

    public function testStoreActionIsNotBoolean(): void
    {
        $this->assertFalse(OptionAction::Store->isBoolean());
    }

    public function testStoreTrueActionDefaultValue(): void
    {
        $this->assertTrue(OptionAction::StoreTrue->getDefaultValue());
    }

    public function testStoreFalseActionDefaultValue(): void
    {
        $this->assertFalse(OptionAction::StoreFalse->getDefaultValue());
    }

    public function testCountActionDefaultValue(): void
    {
        $this->assertSame(1, OptionAction::Count->getDefaultValue());
    }

    public function testStoreActionDefaultValue(): void
    {
        $this->assertNull(OptionAction::Store->getDefaultValue());
    }

    public function testStoreActionExecute(): void
    {
        $result = OptionAction::Store->execute('value', null);
        $this->assertSame('value', $result);
    }

    public function testStoreTrueActionExecute(): void
    {
        $result = OptionAction::StoreTrue->execute(null, null);
        $this->assertTrue($result);
    }

    public function testStoreFalseActionExecute(): void
    {
        $result = OptionAction::StoreFalse->execute(null, null);
        $this->assertFalse($result);
    }

    public function testAppendActionExecuteWithNoCurrentValue(): void
    {
        $result = OptionAction::Append->execute('value', null);
        $this->assertSame(['value'], $result);
    }

    public function testAppendActionExecuteWithExistingValue(): void
    {
        $result = OptionAction::Append->execute('new', ['old']);
        $this->assertSame(['old', 'new'], $result);
    }

    public function testCountActionExecuteWithNoCurrentValue(): void
    {
        $result = OptionAction::Count->execute(1, null);
        $this->assertSame(1, $result);
    }

    public function testCountActionExecuteWithExistingValue(): void
    {
        $result = OptionAction::Count->execute(1, 2);
        $this->assertSame(3, $result);
    }

    public function testStoreConstActionExecute(): void
    {
        $result = OptionAction::StoreConst->execute('const_value', null);
        $this->assertSame('const_value', $result);
    }

    public function testAppendConstActionExecute(): void
    {
        $result = OptionAction::AppendConst->execute('const', ['existing']);
        $this->assertSame(['existing', 'const'], $result);
    }

    public function testCallbackActionExecute(): void
    {
        $result = OptionAction::Callback->execute('callback_value', null);
        $this->assertSame('callback_value', $result);
    }

    public function testHelpActionExecute(): void
    {
        $result = OptionAction::Help->execute(null, null);
        $this->assertTrue($result);
    }

    public function testVersionActionExecute(): void
    {
        $result = OptionAction::Version->execute(null, null);
        $this->assertTrue($result);
    }
}
