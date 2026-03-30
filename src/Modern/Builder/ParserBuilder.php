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

use Horde\Argv\ImmutableParser;
use Horde\Argv\Modern\Config\{ParserConfig, OptionConfig, OptionGroupConfig, ContextConfig};
use Horde\Argv\Modern\Enum\ConflictHandler;

/**
 * Immutable builder for parsers.
 *
 * Following Principle #7: Immutable Throughout
 * Each method clones and returns new builder (~4μs overhead, negligible).
 *
 * Following Principle #5: Modern API is Immutable and Explicit
 * Builders only, no array configuration in Modern API.
 *
 * Usage:
 *   $parser = ParserBuilder::create()
 *       ->withUsage('%prog [options] <file>')
 *       ->withDescription('Process files')
 *       ->addOption(...)
 *       ->build();
 *
 * @category Horde
 * @package  Argv
 */
class ParserBuilder
{
    private ParserConfig $config;
    private array $options = [];
    private array $groups = [];
    private array $contexts = [];
    private mixed $helpFormatter = null;  // HelpFormatter|null

    private function __construct()
    {
        $this->config = new ParserConfig();
    }

    /**
     * Create new builder instance.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set usage string.
     *
     * @param string $usage Usage string (e.g., '%prog [options] <file>')
     * @return self New builder instance
     */
    public function withUsage(string $usage): self
    {
        $new = clone $this;
        $new->config = $new->config->with('usage', $usage);
        return $new;
    }

    /**
     * Set description.
     *
     * @param string $description Description text
     * @return self New builder instance
     */
    public function withDescription(string $description): self
    {
        $new = clone $this;
        $new->config = $new->config->with('description', $description);
        return $new;
    }

    /**
     * Set version string.
     *
     * @param string $version Version string
     * @return self New builder instance
     */
    public function withVersion(string $version): self
    {
        $new = clone $this;
        $new->config = $new->config->with('version', $version);
        return $new;
    }

    /**
     * Set epilog (text after options in help).
     *
     * @param string $epilog Epilog text
     * @return self New builder instance
     */
    public function withEpilog(string $epilog): self
    {
        $new = clone $this;
        $new->config = $new->config->with('epilog', $epilog);
        return $new;
    }

    /**
     * Set program name (defaults to script name).
     *
     * @param string $prog Program name
     * @return self New builder instance
     */
    public function withProg(string $prog): self
    {
        $new = clone $this;
        $new->config = $new->config->with('prog', $prog);
        return $new;
    }

    /**
     * Allow options to be interspersed with arguments.
     *
     * @param bool $allow Whether to allow interspersed args
     * @return self New builder instance
     */
    public function allowInterspersedArgs(bool $allow = true): self
    {
        $new = clone $this;
        $new->config = $new->config->with('allowInterspersedArgs', $allow);
        return $new;
    }

    /**
     * Allow unknown options (don't error).
     *
     * @param bool $allow Whether to allow unknown args
     * @return self New builder instance
     */
    public function allowUnknownArgs(bool $allow = true): self
    {
        $new = clone $this;
        $new->config = $new->config->with('allowUnknownArgs', $allow);
        return $new;
    }

    /**
     * Ignore unknown options (don't include in result).
     *
     * @param bool $ignore Whether to ignore unknown args
     * @return self New builder instance
     */
    public function ignoreUnknownArgs(bool $ignore = true): self
    {
        $new = clone $this;
        $new->config = $new->config->with('ignoreUnknownArgs', $ignore);
        return $new;
    }

    /**
     * Add automatic --help option.
     *
     * @param bool $add Whether to add help option
     * @return self New builder instance
     */
    public function addHelpOption(bool $add = true): self
    {
        $new = clone $this;
        $new->config = $new->config->with('addHelpOption', $add);
        return $new;
    }

    /**
     * Set conflict resolution handler.
     *
     * @param ConflictHandler $handler Conflict handler
     * @return self New builder instance
     */
    public function conflictHandler(ConflictHandler $handler): self
    {
        $new = clone $this;
        $new->config = $new->config->with('conflictHandler', $handler);
        return $new;
    }

    /**
     * Add an option.
     *
     * @param OptionConfig $option Option to add
     * @return self New builder instance
     */
    public function addOption(OptionConfig $option): self
    {
        $new = clone $this;
        $new->options[] = $option;
        return $new;
    }

    /**
     * Add multiple options.
     *
     * @param array<OptionConfig> $options Options to add
     * @return self New builder instance
     */
    public function addOptions(array $options): self
    {
        $new = clone $this;
        foreach ($options as $option) {
            $new->options[] = $option;
        }
        return $new;
    }

    /**
     * Add an option group.
     *
     * @param OptionGroupConfig $group Group to add
     * @return self New builder instance
     */
    public function addGroup(OptionGroupConfig $group): self
    {
        $new = clone $this;
        $new->groups[] = $group;
        return $new;
    }

    /**
     * Add a context (subcommand).
     *
     * Contexts provide command-specific option sets that don't conflict.
     * Example: git commit --amend vs git push --force
     *
     * @param ContextConfig $context Context to add
     * @return self New builder instance
     */
    public function addContext(ContextConfig $context): self
    {
        $new = clone $this;
        $new->contexts[] = $context;
        return $new;
    }

    /**
     * Add multiple contexts.
     *
     * @param array<ContextConfig> $contexts Contexts to add
     * @return self New builder instance
     */
    public function addContexts(array $contexts): self
    {
        $new = clone $this;
        foreach ($contexts as $context) {
            $new->contexts[] = $context;
        }
        return $new;
    }

    /**
     * Set help formatter (optional, avoids circular dependencies).
     *
     * Note: Formatter can also be provided at parse time to avoid
     * circular dependencies during application setup.
     *
     * @param mixed $formatter HelpFormatter instance
     * @return self New builder instance
     */
    public function withHelpFormatter(mixed $formatter): self
    {
        $new = clone $this;
        $new->helpFormatter = $formatter;
        return $new;
    }

    /**
     * Build immutable parser.
     *
     * @return ImmutableParser Immutable parser instance
     */
    public function build(): ImmutableParser
    {
        return new ImmutableParser(
            $this->config,
            $this->options,
            $this->groups,
            $this->helpFormatter,
            $this->contexts
        );
    }

    /**
     * Initialize from existing config (internal use for use-and-amend pattern).
     *
     * Used by ImmutableParser->toBuilder() to enable copy-and-modify pattern.
     *
     * @param ParserConfig $config Parser configuration
     * @return self New builder instance
     */
    public function fromConfig(ParserConfig $config): self
    {
        $new = clone $this;
        $new->config = $config;
        return $new;
    }

    /**
     * Set options (internal use for use-and-amend pattern).
     *
     * @param array<OptionConfig> $options Options array
     * @return self New builder instance
     */
    public function setOptions(array $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    /**
     * Set groups (internal use for use-and-amend pattern).
     *
     * @param array<OptionGroupConfig> $groups Groups array
     * @return self New builder instance
     */
    public function setGroups(array $groups): self
    {
        $new = clone $this;
        $new->groups = $groups;
        return $new;
    }

    /**
     * Set contexts (internal use for use-and-amend pattern).
     *
     * @param array<ContextConfig> $contexts Contexts array
     * @return self New builder instance
     */
    public function setContexts(array $contexts): self
    {
        $new = clone $this;
        $new->contexts = $contexts;
        return $new;
    }
}
