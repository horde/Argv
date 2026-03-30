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

namespace Horde\Argv\Modern\Result;

/**
 * Immutable option values container.
 *
 * Following Principle #6: Explicit Access Over Clever Syntax
 * No ArrayAccess - use explicit getter methods for clarity.
 *
 * Following Principle #7: Immutable Throughout
 *
 * @category Horde
 * @package  Argv
 */
readonly class OptionValues
{
    /**
     * Constructor.
     *
     * @param array<string, mixed> $values Parsed option values
     */
    public function __construct(
        private array $values = [],
    ) {}

    /**
     * Get option value with optional default.
     *
     * @param string $name Option name (destination)
     * @param mixed $default Default value if option not set
     * @return mixed Option value or default
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }

    /**
     * Check if option was provided.
     *
     * Note: This checks if the key exists, even if value is null.
     * Use this to distinguish "not provided" from "provided with null value".
     *
     * @param string $name Option name (destination)
     * @return bool True if option was set
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    /**
     * Get all values as associative array.
     *
     * @return array<string, mixed> All option values
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Get all option names.
     *
     * @return array<int, string> List of option names
     */
    public function keys(): array
    {
        return array_keys($this->values);
    }

    /**
     * Check if any options were parsed.
     *
     * @return bool True if no options were set
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * Count number of options.
     *
     * @return int Number of options set
     */
    public function count(): int
    {
        return count($this->values);
    }
}
