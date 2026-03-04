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
 * Supports contextual option groups (subcommands):
 * - globalOptions: Options available across all contexts
 * - contextOptions: Options specific to activated context
 * - context: Name of activated context (null if no context)
 * - arguments: Positional arguments (after context name if context active)
 *
 * Backward compatible: When no contexts used, all options in $options property.
 *
 * @category Horde
 * @package  Argv
 */
readonly class ParseResult
{
    /**
     * Constructor.
     *
     * @param OptionValues $options Parsed option values (legacy, or global when no context)
     * @param array<int, string> $arguments Positional arguments
     * @param array<int, string> $unknown Unknown options (if allowed)
     * @param OptionValues|null $globalOptions Global options (when contexts used)
     * @param OptionValues|null $contextOptions Context-specific options (when context active)
     * @param string|null $context Activated context name (null if no context)
     */
    public function __construct(
        public OptionValues $options,
        public array $arguments,
        public array $unknown = [],
        public ?OptionValues $globalOptions = null,
        public ?OptionValues $contextOptions = null,
        public ?string $context = null,
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
     * Check if a context is active.
     *
     * @return bool True if context was activated
     */
    public function hasContext(): bool
    {
        return $this->context !== null;
    }

    /**
     * Get the active context name.
     *
     * @return string|null Context name or null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Convenience method for accessing options.
     *
     * When contexts are used:
     * - Checks contextOptions first (if context active)
     * - Falls back to globalOptions
     *
     * When no contexts:
     * - Returns from options property (backward compatible)
     *
     * Shorthand for $result->options->get($name, $default)
     *
     * @param string $name Option name (destination)
     * @param mixed $default Default value if option not set
     * @return mixed Option value or default
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        // Context mode: check context first, then global
        if ($this->hasContext() && $this->contextOptions !== null) {
            if ($this->contextOptions->has($name)) {
                return $this->contextOptions->get($name, $default);
            }
            if ($this->globalOptions !== null) {
                return $this->globalOptions->get($name, $default);
            }
            return $default;
        }

        // Legacy mode: use options property
        return $this->options->get($name, $default);
    }

    /**
     * Check if an option exists.
     *
     * When contexts are used:
     * - Checks contextOptions first (if context active)
     * - Then checks globalOptions
     *
     * When no contexts:
     * - Checks options property (backward compatible)
     *
     * @param string $name Option name (destination)
     * @return bool True if option exists
     */
    public function hasOption(string $name): bool
    {
        // Context mode: check context first, then global
        if ($this->hasContext() && $this->contextOptions !== null) {
            if ($this->contextOptions->has($name)) {
                return true;
            }
            if ($this->globalOptions !== null) {
                return $this->globalOptions->has($name);
            }
            return false;
        }

        // Legacy mode: use options property
        return $this->options->has($name);
    }
}
