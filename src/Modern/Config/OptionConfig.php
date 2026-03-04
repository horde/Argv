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

namespace Horde\Argv\Modern\Config;

use Horde\Argv\Modern\Enum\{OptionAction, OptionType};
use Horde\Argv\Modern\Exception\InvalidOptionException;

/**
 * Immutable option configuration.
 *
 * Following Principle #3: Provide Infrastructure, Don't Mandate
 * Validators and transformers are optional.
 *
 * Following Principle #4: Explicit Type Contracts
 * Type specified = that type guaranteed or error.
 *
 * @category Horde
 * @package  Argv
 */
readonly class OptionConfig
{
    /**
     * Constructor.
     *
     * @param string $short Short option (e.g., '-v')
     * @param string $long Long option (e.g., '--verbose')
     * @param OptionAction $action How to process this option
     * @param OptionType $type Type validation/conversion
     * @param string $dest Destination name in result (auto-generated if empty)
     * @param mixed $default Default value if option not provided
     * @param mixed $const Constant value for StoreConst/AppendConst actions
     * @param string|null $help Help text
     * @param string|null $metavar Placeholder name in help (e.g., 'FILE')
     * @param array $choices Valid values (enforced if non-empty)
     * @param int $nargs Number of arguments (currently always 1)
     * @param callable|null $callback Callback function for Callback action
     * @param callable|null $validator Optional validator (Principle #3)
     * @param callable|null $map Optional transformer (Principle #3)
     * @throws InvalidOptionException If configuration is invalid
     */
    public function __construct(
        public string $short = '',
        public string $long = '',
        public OptionAction $action = OptionAction::Store,
        public OptionType $type = OptionType::String,
        public string $dest = '',
        public mixed $default = null,
        public mixed $const = null,
        public ?string $help = null,
        public ?string $metavar = null,
        public array $choices = [],
        public int $nargs = 1,
        public mixed $callback = null,     // callable|null (can't type readonly)
        public mixed $validator = null,    // callable|null (can't type readonly - Principle #3)
        public mixed $map = null,          // callable|null (can't type readonly - Principle #3)
    ) {
        $this->validate();
    }

    /**
     * Validate configuration on construction.
     *
     * Ensures option configuration is valid before creating parser.
     * Catches errors early at configuration time rather than parse time.
     *
     * @throws InvalidOptionException If configuration is invalid
     */
    private function validate(): void
    {
        // Must have at least short or long option
        if ($this->short === '' && $this->long === '') {
            throw new InvalidOptionException(
                optionName: '',
                reason: 'Option must have either short or long name',
                context: []
            );
        }

        // Validate short option format
        if ($this->short !== '' && !preg_match('/^-[a-zA-Z0-9]$/', $this->short)) {
            throw new InvalidOptionException(
                optionName: $this->short,
                reason: 'Short option must be format: -X (single character)',
                context: ['short' => $this->short]
            );
        }

        // Validate long option format
        if ($this->long !== '' && !preg_match('/^--[a-zA-Z][a-zA-Z0-9-]*$/', $this->long)) {
            throw new InvalidOptionException(
                optionName: $this->long,
                reason: 'Long option must be format: --name (lowercase, hyphens allowed)',
                context: ['long' => $this->long]
            );
        }

        // Validate choices
        if (!empty($this->choices) && $this->action !== OptionAction::Store && $this->action !== OptionAction::Append) {
            throw new InvalidOptionException(
                optionName: $this->getDisplayName(),
                reason: 'Choices only valid for Store and Append actions',
                context: ['action' => $this->action->value]
            );
        }

        // Validate const for StoreConst/AppendConst
        if (($this->action === OptionAction::StoreConst || $this->action === OptionAction::AppendConst) && $this->const === null) {
            throw new InvalidOptionException(
                optionName: $this->getDisplayName(),
                reason: 'StoreConst/AppendConst actions require const value',
                context: ['action' => $this->action->value]
            );
        }

        // Validate callback for Callback action
        if ($this->action === OptionAction::Callback && $this->callback === null) {
            throw new InvalidOptionException(
                optionName: $this->getDisplayName(),
                reason: 'Callback action requires callback function',
                context: ['action' => $this->action->value]
            );
        }
    }

    /**
     * Get destination name for storing parsed value.
     *
     * Auto-generates from long or short option if not specified.
     *
     * @return string Destination name
     */
    public function getDestination(): string
    {
        if ($this->dest !== '') {
            return $this->dest;
        }

        // Auto-generate from long option
        if ($this->long !== '') {
            return ltrim($this->long, '-');
        }

        // Fall back to short option
        return ltrim($this->short, '-');
    }

    /**
     * Get display name for error messages.
     *
     * Prefers long option over short for clarity.
     *
     * @return string Display name
     */
    public function getDisplayName(): string
    {
        if ($this->long !== '') {
            return $this->long;
        }
        return $this->short;
    }
}
