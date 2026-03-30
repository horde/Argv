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

namespace Horde\Argv\Modern\Builder;

use Horde\Argv\Modern\Config\ContextConfig;
use Horde\Argv\Modern\Config\OptionConfig;
use Horde\Argv\Modern\Config\OptionGroupConfig;

/**
 * Immutable builder for context configurations.
 *
 * Contexts represent subcommands with their own option sets.
 *
 * Example:
 * ```php
 * $deploy = ContextBuilder::create('deploy')
 *     ->withDescription('Deploy application')
 *     ->addOption($forceOption)
 *     ->requiresArguments(1, 'environment')
 *     ->build();
 * ```
 *
 * Following Principle #5: Immutable and Explicit
 * Following Principle #3: Immutable Throughout
 *
 * @category Horde
 * @package  Argv
 */
class ContextBuilder
{
    /**
     * Constructor.
     *
     * @param string $name Context name
     * @param string $description Description
     * @param string $usage Usage string
     * @param string $help Help text
     * @param array<string> $aliases Alternative names
     * @param array<OptionConfig> $options Context-specific options
     * @param array<OptionGroupConfig> $groups Option groups
     * @param int $minArgs Minimum arguments
     * @param int $maxArgs Maximum arguments
     * @param string $argsDescription Argument description
     * @param array<ContextConfig> $subContexts Sub-contexts
     */
    private function __construct(
        private string $name,
        private string $description = '',
        private string $usage = '',
        private string $help = '',
        private array $aliases = [],
        private array $options = [],
        private array $groups = [],
        private int $minArgs = 0,
        private int $maxArgs = PHP_INT_MAX,
        private string $argsDescription = '',
        private array $subContexts = [],
    ) {}

    /**
     * Create a new context builder.
     *
     * @param string $name Context name (e.g., 'deploy', 'rollback')
     * @return self New builder instance
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Set context description.
     *
     * @param string $description Description shown in help
     * @return self New builder with description
     */
    public function withDescription(string $description): self
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    /**
     * Set usage string for this context.
     *
     * @param string $usage Usage string (e.g., '%prog deploy [options] <env>')
     * @return self New builder with usage
     */
    public function withUsage(string $usage): self
    {
        $new = clone $this;
        $new->usage = $usage;
        return $new;
    }

    /**
     * Set detailed help text.
     *
     * @param string $help Help text
     * @return self New builder with help
     */
    public function withHelp(string $help): self
    {
        $new = clone $this;
        $new->help = $help;
        return $new;
    }

    /**
     * Set context aliases (alternative names).
     *
     * @param array<string> $aliases Alternative names (e.g., ['dep', 'depl'])
     * @return self New builder with aliases
     */
    public function withAliases(array $aliases): self
    {
        $new = clone $this;
        $new->aliases = $aliases;
        return $new;
    }

    /**
     * Add an option to this context.
     *
     * @param OptionConfig $option Option configuration
     * @return self New builder with option added
     */
    public function addOption(OptionConfig $option): self
    {
        $new = clone $this;
        $new->options = [...$this->options, $option];
        return $new;
    }

    /**
     * Add multiple options to this context.
     *
     * @param array<OptionConfig> $options Option configurations
     * @return self New builder with options added
     */
    public function addOptions(array $options): self
    {
        $new = clone $this;
        $new->options = [...$this->options, ...$options];
        return $new;
    }

    /**
     * Add an option group to this context.
     *
     * @param OptionGroupConfig $group Option group configuration
     * @return self New builder with group added
     */
    public function addGroup(OptionGroupConfig $group): self
    {
        $new = clone $this;
        $new->groups = [...$this->groups, $group];
        return $new;
    }

    /**
     * Require exact number of arguments after context name.
     *
     * @param int $count Required argument count
     * @param string $description Description of arguments
     * @return self New builder with argument requirement
     */
    public function requiresArguments(int $count, string $description = ''): self
    {
        $new = clone $this;
        $new->minArgs = $count;
        $new->maxArgs = $count;
        $new->argsDescription = $description;
        return $new;
    }

    /**
     * Accept a range of arguments after context name.
     *
     * @param int $min Minimum argument count
     * @param int $max Maximum argument count (default: unlimited)
     * @param string $description Description of arguments
     * @return self New builder with argument range
     */
    public function acceptsArguments(
        int $min,
        int $max = PHP_INT_MAX,
        string $description = ''
    ): self {
        $new = clone $this;
        $new->minArgs = $min;
        $new->maxArgs = $max;
        $new->argsDescription = $description;
        return $new;
    }

    /**
     * Require exactly N arguments (alias for requiresArguments).
     *
     * @param int $count Required argument count
     * @return self New builder with exact requirement
     */
    public function requiresExactly(int $count): self
    {
        return $this->requiresArguments($count);
    }

    /**
     * Require at least N arguments.
     *
     * @param int $count Minimum argument count
     * @return self New builder with minimum requirement
     */
    public function requiresAtLeast(int $count): self
    {
        $new = clone $this;
        $new->minArgs = $count;
        $new->maxArgs = PHP_INT_MAX;
        return $new;
    }

    /**
     * Accept at most N arguments.
     *
     * @param int $count Maximum argument count
     * @return self New builder with maximum limit
     */
    public function requiresAtMost(int $count): self
    {
        $new = clone $this;
        $new->minArgs = 0;
        $new->maxArgs = $count;
        return $new;
    }

    /**
     * Add a sub-context (nested command).
     *
     * @param ContextConfig $context Sub-context configuration
     * @return self New builder with sub-context added
     */
    public function addContext(ContextConfig $context): self
    {
        $new = clone $this;
        $new->subContexts = [...$this->subContexts, $context];
        return $new;
    }

    /**
     * Build the immutable context configuration.
     *
     * @return ContextConfig Immutable context config
     */
    public function build(): ContextConfig
    {
        return new ContextConfig(
            name: $this->name,
            description: $this->description,
            usage: $this->usage,
            help: $this->help,
            aliases: $this->aliases,
            options: $this->options,
            groups: $this->groups,
            minArgs: $this->minArgs,
            maxArgs: $this->maxArgs,
            argsDescription: $this->argsDescription,
            subContexts: $this->subContexts,
        );
    }
}
