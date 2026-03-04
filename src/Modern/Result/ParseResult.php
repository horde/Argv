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

namespace Horde\Argv\Modern\Result;

/**
 * Immutable parse result.
 *
 * Following Principle #6: Explicit Access Over Clever Syntax
 * Clear separation: options vs arguments vs unknown.
 * No ArrayAccess to avoid ambiguity.
 *
 * Following Principle #7: Immutable Throughout
 *
 * @category Horde
 * @package  Argv
 */
readonly class ParseResult
{
    /**
     * Constructor.
     *
     * @param OptionValues $options Parsed option values
     * @param array<int, string> $arguments Positional arguments
     * @param array<int, string> $unknown Unknown options (if allowed)
     */
    public function __construct(
        public OptionValues $options,
        public array $arguments,
        public array $unknown = [],
    ) {
    }

    /**
     * Check if unknown options were encountered.
     *
     * @return bool True if any unknown options
     */
    public function hasUnknown(): bool
    {
        return count($this->unknown) > 0;
    }

    /**
     * Check if positional arguments were provided.
     *
     * @return bool True if any arguments
     */
    public function hasArguments(): bool
    {
        return count($this->arguments) > 0;
    }

    /**
     * Convenience method for accessing options.
     *
     * Shorthand for $result->options->get($name, $default)
     *
     * @param string $name Option name (destination)
     * @param mixed $default Default value if option not set
     * @return mixed Option value or default
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options->get($name, $default);
    }
}
