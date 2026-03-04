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

/**
 * Immutable context configuration.
 *
 * Contexts represent subcommands or command modes that have their own
 * set of options. Options in different contexts don't conflict with each other.
 *
 * Example: git commit --amend vs git push --force
 *          These --force options are different and context-specific.
 *
 * Following Principle #7: Immutable Throughout
 *
 * @category Horde
 * @package  Argv
 */
readonly class ContextConfig
{
    /**
     * Constructor.
     *
     * @param string $name Context name (e.g., 'deploy', 'rollback')
     * @param string $description Description shown in help
     * @param string $usage Usage string for this context
     * @param string $help Detailed help text
     * @param array<string> $aliases Alternative names for this context
     * @param array<OptionConfig> $options Options specific to this context
     * @param array<OptionGroupConfig> $groups Option groups for this context
     * @param int $minArgs Minimum required arguments after context name
     * @param int $maxArgs Maximum allowed arguments after context name
     * @param string $argsDescription Description of expected arguments
     * @param array<ContextConfig> $subContexts Nested sub-contexts
     */
    public function __construct(
        public string $name,
        public string $description = '',
        public string $usage = '',
        public string $help = '',
        public array $aliases = [],
        public array $options = [],
        public array $groups = [],
        public int $minArgs = 0,
        public int $maxArgs = PHP_INT_MAX,
        public string $argsDescription = '',
        public array $subContexts = [],
    ) {
        $this->validate();
    }

    /**
     * Validate context configuration.
     *
     * @throws \InvalidArgumentException If configuration is invalid
     */
    private function validate(): void
    {
        if ($this->name === '') {
            throw new \InvalidArgumentException('Context name cannot be empty');
        }

        if ($this->minArgs < 0) {
            throw new \InvalidArgumentException('minArgs must be >= 0');
        }

        if ($this->maxArgs < $this->minArgs) {
            throw new \InvalidArgumentException('maxArgs must be >= minArgs');
        }

        // Validate all options
        foreach ($this->options as $option) {
            if (!$option instanceof OptionConfig) {
                throw new \InvalidArgumentException('All options must be OptionConfig instances');
            }
        }

        // Validate all groups
        foreach ($this->groups as $group) {
            if (!$group instanceof OptionGroupConfig) {
                throw new \InvalidArgumentException('All groups must be OptionGroupConfig instances');
            }
        }

        // Validate all sub-contexts
        foreach ($this->subContexts as $subContext) {
            if (!$subContext instanceof ContextConfig) {
                throw new \InvalidArgumentException('All sub-contexts must be ContextConfig instances');
            }
        }

        // Validate aliases don't conflict with name
        if (in_array($this->name, $this->aliases, true)) {
            throw new \InvalidArgumentException('Context name cannot be in aliases list');
        }
    }

    /**
     * Check if a given name matches this context.
     *
     * @param string $name Name to check
     * @return bool True if name matches context name or any alias
     */
    public function matches(string $name): bool
    {
        return $this->name === $name || in_array($name, $this->aliases, true);
    }

    /**
     * Get all valid names for this context.
     *
     * @return array<string> Context name plus all aliases
     */
    public function getAllNames(): array
    {
        return [$this->name, ...$this->aliases];
    }

    /**
     * Find a sub-context by name.
     *
     * @param string $name Sub-context name
     * @return ContextConfig|null Sub-context or null if not found
     */
    public function findSubContext(string $name): ?ContextConfig
    {
        foreach ($this->subContexts as $subContext) {
            if ($subContext->matches($name)) {
                return $subContext;
            }
        }
        return null;
    }

    /**
     * Check if this context has sub-contexts.
     *
     * @return bool True if sub-contexts exist
     */
    public function hasSubContexts(): bool
    {
        return count($this->subContexts) > 0;
    }

    /**
     * Validate argument count against context requirements.
     *
     * @param int $count Actual argument count
     * @return bool True if count is valid
     */
    public function validateArgumentCount(int $count): bool
    {
        return $count >= $this->minArgs && $count <= $this->maxArgs;
    }

    /**
     * Get argument count error message.
     *
     * @param int $actualCount Actual argument count
     * @return string Error message
     */
    public function getArgumentCountError(int $actualCount): string
    {
        if ($this->minArgs === $this->maxArgs) {
            return sprintf(
                'Context "%s" requires exactly %d argument%s, got %d',
                $this->name,
                $this->minArgs,
                $this->minArgs === 1 ? '' : 's',
                $actualCount
            );
        }

        if ($actualCount < $this->minArgs) {
            return sprintf(
                'Context "%s" requires at least %d argument%s, got %d',
                $this->name,
                $this->minArgs,
                $this->minArgs === 1 ? '' : 's',
                $actualCount
            );
        }

        return sprintf(
            'Context "%s" accepts at most %d argument%s, got %d',
            $this->name,
            $this->maxArgs,
            $this->maxArgs === 1 ? '' : 's',
            $actualCount
        );
    }
}
