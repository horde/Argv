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

namespace Horde\Argv;

use Horde\Argv\Modern\Config\{ParserConfig, OptionConfig, OptionGroupConfig, ContextConfig};
use Horde\Argv\Modern\Result\{ParseResult, OptionValues};
use Horde\Argv\Modern\Exception\{
    InvalidOptionException,
    AmbiguousOptionException,
    MissingValueException,
    ConflictingOptionException,
    UnknownContextException,
    InvalidArgumentCountException
};
use Horde\Argv\Modern\Enum\{OptionAction, ConflictHandler};

/**
 * Immutable argument parser for Modern API.
 *
 * Following Principle #5: Modern API is Immutable and Explicit
 * Parser is configured once via constructor/builder and cannot be modified.
 *
 * Following Principle #7: Immutable Throughout
 * All state is readonly. Parser can be reconstructed via toBuilder() pattern.
 *
 * Usage:
 *   $parser = ParserBuilder::create()
 *       ->withUsage('%prog [options] <file>')
 *       ->addOption($option)
 *       ->build();
 *
 *   $result = $parser->parse($argv);
 *   $verbose = $result->options->get('verbose', false);
 *
 * @category Horde
 * @package  Argv
 */
readonly class ImmutableParser implements ArgvParser
{
    private array $optionMap;      // option string => OptionConfig
    private array $optionsByDest;  // dest name => OptionConfig
    private array $contextMap;     // context name => ContextConfig

    /**
     * Construct immutable parser.
     *
     * @param ParserConfig $config Parser configuration
     * @param array<OptionConfig> $options Global options (available in all contexts)
     * @param array<OptionGroupConfig> $groups Option groups (for help formatting)
     * @param mixed $helpFormatter Optional help formatter
     * @param array<ContextConfig> $contexts Available contexts (subcommands)
     */
    public function __construct(
        private ParserConfig $config,
        private array $options = [],
        private array $groups = [],
        private mixed $helpFormatter = null,
        private array $contexts = [],
    ) {
        $this->optionMap = $this->buildOptionMap();
        $this->optionsByDest = $this->buildDestMap();
        $this->contextMap = $this->buildContextMap();
        $this->checkConflicts();
    }

    /**
     * Parse arguments.
     *
     * Supports both legacy (no contexts) and modern (with contexts) parsing.
     *
     * @param array<string>|null $argv Arguments to parse (null = do not use $_SERVER['argv'])
     * @return ParseResult Immutable parse result
     */
    public function parse(?array $argv = null): ParseResult
    {
        if ($argv === null) {
            $argv = $_SERVER['argv'] ?? [];
            // Remove script name if present
            if (isset($argv[0])) {
                array_shift($argv);
            }
        }

        // If no contexts registered, use legacy parsing
        if (empty($this->contexts)) {
            return $this->parseLegacy($argv);
        }

        // Otherwise use context-aware parsing
        return $this->parseWithContexts($argv);
    }

    /**
     * Legacy parsing (no contexts).
     *
     * @param array<string> $argv Arguments to parse
     * @return ParseResult Parse result
     */
    private function parseLegacy(array $argv): ParseResult
    {
        $values = [];
        $args = [];
        $unknown = [];

        // Initialize defaults
        foreach ($this->options as $option) {
            $dest = $this->getDestination($option);
            if ($option->default !== null) {
                $values[$dest] = $option->default;
            }
        }

        $rargs = $argv;
        $largs = [];

        while (!empty($rargs)) {
            $arg = array_shift($rargs);

            // Check for end of options marker
            if ($arg === '--') {
                $largs = array_merge($largs, $rargs);
                break;
            }

            // Check if it looks like an option
            if ($this->isOption($arg)) {
                $this->processOption($arg, $rargs, $values, $unknown);
            } else {
                // Positional argument
                if ($this->config->allowInterspersedArgs) {
                    $largs[] = $arg;
                } else {
                    // Stop processing options
                    $largs[] = $arg;
                    $largs = array_merge($largs, $rargs);
                    break;
                }
            }
        }

        $args = $largs;

        // Filter unknown if ignoreUnknownArgs is set
        if ($this->config->ignoreUnknownArgs) {
            $unknown = [];
        }

        return new ParseResult(
            options: new OptionValues($values),
            arguments: $args,
            unknown: $unknown
        );
    }

    /**
     * Context-aware parsing.
     *
     * Parse global options, detect context, then parse context-specific options.
     *
     * @param array<string> $argv Arguments to parse
     * @return ParseResult Parse result with context information
     */
    private function parseWithContexts(array $argv): ParseResult
    {
        $globalValues = [];
        $contextValues = [];
        $args = [];
        $unknown = [];
        $activeContext = null;

        // Initialize global option defaults
        foreach ($this->options as $option) {
            $dest = $this->getDestination($option);
            if ($option->default !== null) {
                $globalValues[$dest] = $option->default;
            }
        }

        $rargs = $argv;
        $largs = [];
        $contextDetected = false;

        // Phase 1: Parse global options and detect context
        while (!empty($rargs)) {
            $arg = array_shift($rargs);

            // Check for end of options marker
            if ($arg === '--') {
                $largs = array_merge($largs, $rargs);
                break;
            }

            // Check if it looks like an option
            if ($this->isOption($arg)) {
                // Process as global option
                $this->processOption($arg, $rargs, $globalValues, $unknown);
            } else {
                // First positional arg might be a context
                if (!$contextDetected && isset($this->contextMap[$arg])) {
                    $activeContext = $this->contextMap[$arg];
                    $contextDetected = true;

                    // Initialize context option defaults
                    foreach ($activeContext->options as $option) {
                        $dest = $this->getDestination($option);
                        if ($option->default !== null) {
                            $contextValues[$dest] = $option->default;
                        }
                    }

                    // Phase 2: Parse context-specific options and arguments
                    while (!empty($rargs)) {
                        $arg = array_shift($rargs);

                        // Check for end of options marker
                        if ($arg === '--') {
                            $largs = array_merge($largs, $rargs);
                            break;
                        }

                        // Check if it looks like an option
                        if ($this->isOption($arg)) {
                            // Try context option first, fallback to global
                            if (!$this->processContextOption($arg, $rargs, $contextValues, $activeContext, $unknown)) {
                                // Not a context option, try global
                                $this->processOption($arg, $rargs, $globalValues, $unknown);
                            }
                        } else {
                            // Positional argument for context
                            $largs[] = $arg;
                        }
                    }
                    break;
                } else {
                    // Regular positional argument (no context detected)
                    $largs[] = $arg;
                }
            }
        }

        $args = $largs;

        // Validate argument count if context is active
        if ($activeContext !== null) {
            $argCount = count($args);
            if (!$activeContext->validateArgumentCount($argCount)) {
                throw new InvalidArgumentCountException(
                    $activeContext->name,
                    $argCount,
                    $activeContext->minArgs,
                    $activeContext->maxArgs
                );
            }
        }

        // Filter unknown if ignoreUnknownArgs is set
        if ($this->config->ignoreUnknownArgs) {
            $unknown = [];
        }

        // Build result based on whether context was detected
        if ($activeContext !== null) {
            return new ParseResult(
                options: new OptionValues([]), // Not used in context mode
                arguments: $args,
                unknown: $unknown,
                globalOptions: new OptionValues($globalValues),
                contextOptions: new OptionValues($contextValues),
                context: $activeContext->name
            );
        } else {
            // No context detected, return as global options
            return new ParseResult(
                options: new OptionValues($globalValues),
                arguments: $args,
                unknown: $unknown,
                globalOptions: new OptionValues($globalValues),
                contextOptions: new OptionValues([]),
                context: null
            );
        }
    }

    /**
     * Process a context-specific option.
     *
     * @param string $arg Option argument
     * @param array &$rargs Remaining arguments (by reference)
     * @param array &$values Context values (by reference)
     * @param ContextConfig $context Active context
     * @param array &$unknown Unknown options (by reference)
     * @return bool True if option was processed, false if not found in context
     */
    private function processContextOption(
        string $arg,
        array &$rargs,
        array &$values,
        ContextConfig $context,
        array &$unknown
    ): bool {
        // Build context option map
        $contextOptionMap = [];
        foreach ($context->options as $option) {
            if ($option->short !== '') {
                $contextOptionMap[$option->short] = $option;
            }
            if ($option->long !== '') {
                $contextOptionMap[$option->long] = $option;
            }
        }

        // Try to find option in context
        if (str_starts_with($arg, '--')) {
            return $this->processLongContextOption($arg, $rargs, $values, $contextOptionMap, $unknown);
        } else {
            return $this->processShortContextOption($arg, $rargs, $values, $contextOptionMap, $unknown);
        }
    }

    /**
     * Process long context option.
     *
     * @param string $arg Long option
     * @param array &$rargs Remaining arguments
     * @param array &$values Current values
     * @param array<string, OptionConfig> $contextOptionMap Context option map
     * @param array &$unknown Unknown options
     * @return bool True if processed
     */
    private function processLongContextOption(
        string $arg,
        array &$rargs,
        array &$values,
        array $contextOptionMap,
        array &$unknown
    ): bool {
        // Handle --option=value format
        $equals_pos = strpos($arg, '=');
        if ($equals_pos !== false) {
            $opt = substr($arg, 0, $equals_pos);
            $value = substr($arg, $equals_pos + 1);
        } else {
            $opt = $arg;
            $value = null;
        }

        // Find matching option in context
        $option = $this->findContextOption($opt, $contextOptionMap);
        if ($option === null) {
            return false; // Not found in context
        }

        // Get value if needed
        if ($option->action->takesValue()) {
            if ($value === null) {
                if (empty($rargs)) {
                    throw new MissingValueException($opt);
                }
                $value = array_shift($rargs);
            }

            // Type conversion and validation
            $value = $this->convertAndValidate($option, $value);
        } else {
            $value = null;
        }

        // Execute action
        $this->executeAction($option, $value, $values);

        return true;
    }

    /**
     * Process short context option.
     *
     * @param string $arg Short option
     * @param array &$rargs Remaining arguments
     * @param array &$values Current values
     * @param array<string, OptionConfig> $contextOptionMap Context option map
     * @param array &$unknown Unknown options
     * @return bool True if processed
     */
    private function processShortContextOption(
        string $arg,
        array &$rargs,
        array &$values,
        array $contextOptionMap,
        array &$unknown
    ): bool {
        // Handle -abc bundling
        $chars = substr($arg, 1);

        foreach (str_split($chars) as $i => $char) {
            $opt = '-' . $char;

            // Find option in context
            if (!isset($contextOptionMap[$opt])) {
                return false; // Not found in context
            }

            $option = $contextOptionMap[$opt];

            // Get value if needed
            if ($option->action->takesValue()) {
                // Check if value is bundled (-ovalue)
                if ($i < strlen($chars) - 1) {
                    $value = substr($chars, $i + 1);
                } elseif (!empty($rargs)) {
                    $value = array_shift($rargs);
                } else {
                    throw new MissingValueException($opt);
                }

                // Type conversion and validation
                $value = $this->convertAndValidate($option, $value);

                // Execute action
                $this->executeAction($option, $value, $values);

                // Stop processing bundled options after taking a value
                break;
            } else {
                // Flag option, continue with bundling
                $this->executeAction($option, null, $values);
            }
        }

        return true;
    }

    /**
     * Find option in context option map.
     *
     * @param string $opt Option string
     * @param array<string, OptionConfig> $contextOptionMap Context option map
     * @return OptionConfig|null Found option or null
     */
    private function findContextOption(string $opt, array $contextOptionMap): ?OptionConfig
    {
        // Exact match
        if (isset($contextOptionMap[$opt])) {
            return $contextOptionMap[$opt];
        }

        // Partial match for long options
        if (str_starts_with($opt, '--')) {
            $matches = [];
            foreach (array_keys($contextOptionMap) as $key) {
                if (str_starts_with($key, $opt)) {
                    $matches[] = $key;
                }
            }

            if (count($matches) === 1) {
                return $contextOptionMap[$matches[0]];
            } elseif (count($matches) > 1) {
                throw new AmbiguousOptionException($opt, $matches);
            }
        }

        return null;
    }

    /**
     * Check if string looks like an option.
     *
     * @param string $arg Argument to check
     * @return bool True if looks like option
     */
    private function isOption(string $arg): bool
    {
        return str_starts_with($arg, '-') && $arg !== '-';
    }

    /**
     * Process an option argument.
     *
     * @param string $arg Option argument
     * @param array &$rargs Remaining arguments (by reference)
     * @param array &$values Current values (by reference)
     * @param array &$unknown Unknown options (by reference)
     */
    private function processOption(
        string $arg,
        array &$rargs,
        array &$values,
        array &$unknown
    ): void {
        if (str_starts_with($arg, '--')) {
            $this->processLongOption($arg, $rargs, $values, $unknown);
        } else {
            $this->processShortOption($arg, $rargs, $values, $unknown);
        }
    }

    /**
     * Process long option (--option).
     *
     * @param string $arg Long option
     * @param array &$rargs Remaining arguments
     * @param array &$values Current values
     * @param array &$unknown Unknown options
     */
    private function processLongOption(
        string $arg,
        array &$rargs,
        array &$values,
        array &$unknown
    ): void {
        // Handle --option=value format
        $equals_pos = strpos($arg, '=');
        if ($equals_pos !== false) {
            $opt = substr($arg, 0, $equals_pos);
            $value = substr($arg, $equals_pos + 1);
        } else {
            $opt = $arg;
            $value = null;
        }

        // Find matching option
        $option = $this->findOption($opt, $unknown);
        if ($option === null) {
            return;
        }

        // Get value if needed
        if ($option->action->takesValue()) {
            if ($value === null) {
                if (empty($rargs)) {
                    throw new MissingValueException($opt);
                }
                $value = array_shift($rargs);
            }

            // Type conversion and validation
            $value = $this->convertAndValidate($option, $value);
        } else {
            $value = null;
        }

        // Execute action
        $this->executeAction($option, $value, $values);
    }

    /**
     * Process short option(s) (-o or -abc).
     *
     * @param string $arg Short option(s)
     * @param array &$rargs Remaining arguments
     * @param array &$values Current values
     * @param array &$unknown Unknown options
     */
    private function processShortOption(
        string $arg,
        array &$rargs,
        array &$values,
        array &$unknown
    ): void {
        // Strip leading dash
        $opts = substr($arg, 1);

        // Process each character
        $i = 0;
        $len = strlen($opts);
        while ($i < $len) {
            $opt = '-' . $opts[$i];
            $option = $this->findOption($opt, $unknown);

            if ($option === null) {
                $i++;
                continue;
            }

            $value = null;
            if ($option->action->takesValue()) {
                // Check for attached value (-oVALUE)
                if ($i + 1 < $len) {
                    $value = substr($opts, $i + 1);
                    $i = $len; // Consume rest of string
                } else {
                    // Value must be next argument
                    if (empty($rargs)) {
                        throw new MissingValueException($opt);
                    }
                    $value = array_shift($rargs);
                }

                $value = $this->convertAndValidate($option, $value);
            }

            $this->executeAction($option, $value, $values);
            $i++;
        }
    }

    /**
     * Find option by string.
     *
     * @param string $opt Option string
     * @param array &$unknown Unknown options array
     * @return OptionConfig|null Option config or null if not found
     */
    private function findOption(string $opt, array &$unknown): ?OptionConfig
    {
        if (isset($this->optionMap[$opt])) {
            return $this->optionMap[$opt];
        }

        // Check for partial match on long options
        if (str_starts_with($opt, '--')) {
            $matches = [];
            foreach (array_keys($this->optionMap) as $key) {
                if (str_starts_with($key, $opt)) {
                    $matches[] = $key;
                }
            }

            if (count($matches) === 1) {
                return $this->optionMap[$matches[0]];
            }

            if (count($matches) > 1) {
                throw new AmbiguousOptionException($opt, $matches);
            }
        }

        // Unknown option
        if ($this->config->allowUnknownArgs || $this->config->ignoreUnknownArgs) {
            $unknown[] = $opt;
            return null;
        }

        throw new InvalidOptionException($opt, "Unknown option");
    }

    /**
     * Convert and validate option value.
     *
     * @param OptionConfig $option Option configuration
     * @param mixed $value Raw value
     * @return mixed Converted and validated value
     */
    private function convertAndValidate(OptionConfig $option, mixed $value): mixed
    {
        // Type conversion
        $value = $option->type->convert($value);

        // Check choices
        if (!empty($option->choices)) {
            if (!in_array($value, $option->choices, true)) {
                $choices = implode(', ', array_map(
                    fn($c) => "'{$c}'",
                    $option->choices
                ));
                throw new InvalidOptionException(
                    $option->getDisplayName(),
                    "Value must be one of: {$choices}"
                );
            }
        }

        // Custom validator
        if ($option->validator !== null) {
            $result = ($option->validator)($value);
            if ($result !== true) {
                throw new InvalidOptionException(
                    $option->getDisplayName(),
                    "Validation failed: {$result}"
                );
            }
        }

        // Transformer
        if ($option->map !== null) {
            $value = ($option->map)($value);
        }

        return $value;
    }

    /**
     * Execute option action.
     *
     * @param OptionConfig $option Option configuration
     * @param mixed $value Option value
     * @param array &$values Current values
     */
    private function executeAction(
        OptionConfig $option,
        mixed $value,
        array &$values
    ): void {
        $dest = $this->getDestination($option);
        $current = $values[$dest] ?? null;

        if ($option->action === OptionAction::Callback) {
            if ($option->callback === null) {
                throw new InvalidOptionException(
                    $option->getDisplayName(),
                    "Callback action requires a callback function"
                );
            }
            $values[$dest] = ($option->callback)($value, $current);
        } else {
            $values[$dest] = $option->action->execute($value, $current);
        }
    }

    /**
     * Get destination name for option.
     *
     * @param OptionConfig $option Option configuration
     * @return string Destination name
     */
    private function getDestination(OptionConfig $option): string
    {
        return $option->getDestination();
    }

    /**
     * Build map of option strings to option configs.
     *
     * @return array<string, OptionConfig> Option map
     */
    private function buildOptionMap(): array
    {
        $map = [];

        foreach ($this->options as $option) {
            if ($option->short !== '') {
                $map[$option->short] = $option;
            }
            if ($option->long !== '') {
                $map[$option->long] = $option;
            }
        }

        // Add options from groups
        foreach ($this->groups as $group) {
            foreach ($group->options as $option) {
                if ($option->short !== '') {
                    $map[$option->short] = $option;
                }
                if ($option->long !== '') {
                    $map[$option->long] = $option;
                }
            }
        }

        return $map;
    }

    /**
     * Build map of destination names to option configs.
     *
     * @return array<string, OptionConfig> Destination map
     */
    private function buildDestMap(): array
    {
        $map = [];

        foreach ($this->options as $option) {
            $dest = $this->getDestination($option);
            $map[$dest] = $option;
        }

        foreach ($this->groups as $group) {
            foreach ($group->options as $option) {
                $dest = $this->getDestination($option);
                $map[$dest] = $option;
            }
        }

        return $map;
    }

    /**
     * Build map of context names to context configs.
     *
     * @return array<string, ContextConfig> Context map
     */
    private function buildContextMap(): array
    {
        $map = [];

        foreach ($this->contexts as $context) {
            // Map primary name
            $map[$context->name] = $context;
            // Map all aliases
            foreach ($context->aliases as $alias) {
                $map[$alias] = $context;
            }
        }

        return $map;
    }

    /**
     * Check for conflicting options.
     *
     * @throws ConflictingOptionException If conflicts found
     */
    private function checkConflicts(): void
    {
        if ($this->config->conflictHandler === ConflictHandler::Error) {
            $seen = [];
            foreach (array_keys($this->optionMap) as $opt) {
                if (isset($seen[$opt])) {
                    throw new ConflictingOptionException(
                        $opt,
                        "Option conflicts with another option"
                    );
                }
                $seen[$opt] = true;
            }
        }
    }

    /**
     * Convert back to builder for use-and-amend pattern.
     *
     * @return Modern\Builder\ParserBuilder Builder instance
     */
    public function toBuilder(): Modern\Builder\ParserBuilder
    {
        return Modern\Builder\ParserBuilder::create()
            ->fromConfig($this->config)
            ->setOptions($this->options)
            ->setGroups($this->groups)
            ->setContexts($this->contexts);
    }

    /**
     * Format help text.
     *
     * @param Modern\Help\HelpFormatter|null $formatter Optional formatter (uses default if null)
     * @return string Formatted help text
     */
    public function formatHelp(?Modern\Help\HelpFormatter $formatter = null): string
    {
        $formatter ??= $this->helpFormatter ?? Modern\Help\HelpFormatter::create();
        return $formatter->format($this->config, $this->options, $this->groups);
    }
}
