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

namespace Horde\Argv\Modern\Enum;

/**
 * Option actions define how values are processed and stored.
 *
 * Following Principle #2: Enums as Behavior Objects
 * Logic lives with the enum, not scattered in switch statements.
 * Each action knows how to execute itself, whether it takes a value,
 * and what its default behavior is.
 *
 * @category Horde
 * @package  Argv
 */
enum OptionAction: string
{
    case Store = 'store';
    case StoreConst = 'store_const';
    case StoreTrue = 'store_true';
    case StoreFalse = 'store_false';
    case Append = 'append';
    case AppendConst = 'append_const';
    case Count = 'count';
    case Callback = 'callback';
    case Help = 'help';
    case Version = 'version';

    /**
     * Does this action require a value from argv?
     *
     * @return bool True if action needs to consume next argument
     */
    public function takesValue(): bool
    {
        return match ($this) {
            self::Store, self::Append, self::Callback => true,
            default => false,
        };
    }

    /**
     * Is the value required or optional?
     *
     * @return bool True if value is mandatory when action takes a value
     */
    public function requiresArgument(): bool
    {
        return match ($this) {
            self::Store, self::Append => true,
            default => false,
        };
    }

    /**
     * Is this a boolean flag?
     *
     * @return bool True if action represents a boolean flag
     */
    public function isBoolean(): bool
    {
        return match ($this) {
            self::StoreTrue, self::StoreFalse => true,
            default => false,
        };
    }

    /**
     * Get default value for this action when flag is present.
     *
     * @return mixed The default value for this action
     */
    public function getDefaultValue(): mixed
    {
        return match ($this) {
            self::StoreTrue => true,
            self::StoreFalse => false,
            self::Count => 1,
            default => null,
        };
    }

    /**
     * Execute this action with a value.
     *
     * This is the core behavior encapsulation - each action knows
     * how to process its value given the current state.
     *
     * @param mixed $value The value to process
     * @param mixed $current Current value (for Append, Count actions)
     * @return mixed The processed value
     */
    public function execute(mixed $value, mixed $current): mixed
    {
        return match ($this) {
            self::Store => $value,
            self::StoreConst => $value,  // Value is the const
            self::StoreTrue => true,
            self::StoreFalse => false,
            self::Append => [...($current ?? []), $value],
            self::AppendConst => [...($current ?? []), $value],
            self::Count => ($current ?? 0) + 1,
            self::Callback => $value,  // Callback handled separately by parser
            self::Help, self::Version => true,
        };
    }
}
