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

use Horde\Argv\Modern\Exception\ValueValidationException;

/**
 * Option types define validation and conversion behavior.
 *
 * Following Principle #4: Explicit Type Contracts
 * No implicit conversion - type specified = that type guaranteed or error.
 * If no type is specified, values are returned as strings.
 * If a type is specified, the parser WILL return that type or throw an exception.
 *
 * @category Horde
 * @package  Argv
 */
enum OptionType: string
{
    case String = 'string';
    case Int = 'int';
    case Float = 'float';

    /**
     * Validate that a value matches this type.
     *
     * @param mixed $value The value to validate
     * @return bool True if valid
     */
    public function validate(mixed $value): bool
    {
        return match ($this) {
            self::Int => is_numeric($value) && (string)(int)$value === (string)$value,
            self::Float => is_numeric($value),
            self::String => true,  // All values can be strings
        };
    }

    /**
     * Convert a value to this type.
     *
     * Following Principle #4: Explicit contracts - if type specified,
     * return that type or throw. Never return wrong type.
     *
     * IMPORTANT: This method throws if conversion fails. The parser
     * guarantees type safety - you get the declared type or an error.
     *
     * @param mixed $value The value to convert
     * @return mixed The converted value (guaranteed to be correct type)
     * @throws ValueValidationException If conversion fails
     */
    public function convert(mixed $value): mixed
    {
        if (!$this->validate($value)) {
            throw new ValueValidationException(
                optionName: '',  // Will be set by caller
                providedValue: $value,
                expectedType: $this,
                reason: "Value '{$value}' is not a valid {$this->getDescription()}"
            );
        }

        return match ($this) {
            self::Int => (int)$value,
            self::Float => (float)$value,
            self::String => (string)$value,
        };
    }

    /**
     * Get the PHP type name for type hints.
     *
     * @return string PHP type name (int, float, string)
     */
    public function getPhpType(): string
    {
        return match ($this) {
            self::Int => 'int',
            self::Float => 'float',
            self::String => 'string',
        };
    }

    /**
     * Get human-readable type description for error messages.
     *
     * @return string User-friendly type description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::Int => 'integer',
            self::Float => 'floating-point number',
            self::String => 'string',
        };
    }
}
