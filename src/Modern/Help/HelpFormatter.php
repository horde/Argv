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

namespace Horde\Argv\Modern\Help;

use Horde\Argv\Modern\Config\{ParserConfig, OptionConfig, OptionGroupConfig, ContextConfig};

/**
 * Immutable help formatter for Modern API.
 *
 * Following Principle #5: Modern API is Immutable and Explicit
 * Formatter is configured once and produces help text on demand.
 *
 * Usage:
 *   $formatter = HelpFormatter::create()
 *       ->withWidth(80)
 *       ->withIndent(2)
 *       ->build();
 *
 *   $help = $formatter->format($parserConfig, $options, $groups);
 *
 * @category Horde
 * @package  Argv
 */
readonly class HelpFormatter
{
    /**
     * Construct formatter.
     *
     * @param int $width Terminal width (0 = auto-detect)
     * @param int $indent Indentation increment
     * @param int $maxHelpPosition Maximum position before wrapping help text
     * @param bool $shortFirst Show short option first in listings
     */
    public function __construct(
        private int $width = 80,
        private int $indent = 2,
        private int $maxHelpPosition = 24,
        private bool $shortFirst = true,
    ) {
    }

    /**
     * Create formatter with default settings.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set terminal width.
     *
     * @param int $width Width in characters (0 = auto-detect)
     * @return self New formatter instance
     */
    public function withWidth(int $width): self
    {
        return new self($width, $this->indent, $this->maxHelpPosition, $this->shortFirst);
    }

    /**
     * Set indentation increment.
     *
     * @param int $indent Spaces per indent level
     * @return self New formatter instance
     */
    public function withIndent(int $indent): self
    {
        return new self($this->width, $indent, $this->maxHelpPosition, $this->shortFirst);
    }

    /**
     * Set maximum help text position.
     *
     * @param int $position Column position before wrapping
     * @return self New formatter instance
     */
    public function withMaxHelpPosition(int $position): self
    {
        return new self($this->width, $this->indent, $position, $this->shortFirst);
    }

    /**
     * Set option ordering.
     *
     * @param bool $shortFirst True to show short option first
     * @return self New formatter instance
     */
    public function withShortFirst(bool $shortFirst = true): self
    {
        return new self($this->width, $this->indent, $this->maxHelpPosition, $shortFirst);
    }

    /**
     * Build complete formatter (for consistency with builder pattern).
     *
     * @return self
     */
    public function build(): self
    {
        return $this;
    }

    /**
     * Format complete help text.
     *
     * @param ParserConfig $config Parser configuration
     * @param array<OptionConfig> $options Options to format
     * @param array<OptionGroupConfig> $groups Option groups
     * @param array<ContextConfig> $contexts Available contexts
     * @return string Formatted help text
     */
    public function format(
        ParserConfig $config,
        array $options = [],
        array $groups = [],
        array $contexts = []
    ): string {
        $parts = [];

        // Usage
        if ($config->usage !== '' && $config->usage !== null) {
            $parts[] = $this->formatUsage($config->usage, $config->prog);
        }

        // Description
        if ($config->description !== '' && $config->description !== null) {
            $parts[] = $this->formatDescription($config->description);
        }

        // Contexts (if any)
        if (!empty($contexts)) {
            $parts[] = $this->formatContexts($contexts);
        }

        // Options
        if (!empty($options)) {
            $parts[] = $this->formatOptions($options);
        }

        // Groups
        foreach ($groups as $group) {
            $parts[] = $this->formatGroup($group);
        }

        // Epilog
        if ($config->epilog !== '' && $config->epilog !== null) {
            $parts[] = $this->formatEpilog($config->epilog);
        }

        return implode("\n", array_filter($parts)) . "\n";
    }

    /**
     * Format usage line.
     *
     * @param string $usage Usage template
     * @param string|null $prog Program name
     * @return string Formatted usage
     */
    private function formatUsage(string $usage, ?string $prog): string
    {
        $prog ??= 'program';
        $usage = str_replace('%prog', $prog, $usage);
        return "Usage: {$usage}\n";
    }

    /**
     * Format description text.
     *
     * @param string $description Description text
     * @return string Formatted description
     */
    private function formatDescription(string $description): string
    {
        return $this->wrapText($description) . "\n";
    }

    /**
     * Format epilog text.
     *
     * @param string $epilog Epilog text
     * @return string Formatted epilog
     */
    private function formatEpilog(string $epilog): string
    {
        return $this->wrapText($epilog);
    }

    /**
     * Format options section.
     *
     * @param array<OptionConfig> $options Options to format
     * @return string Formatted options
     */
    private function formatOptions(array $options): string
    {
        $lines = ["Options:"];

        foreach ($options as $option) {
            $lines[] = $this->formatOption($option);
        }

        return implode("\n", $lines);
    }

    /**
     * Format option group.
     *
     * @param OptionGroupConfig $group Group to format
     * @return string Formatted group
     */
    private function formatGroup(OptionGroupConfig $group): string
    {
        $lines = [$group->title . ':'];

        if ($group->description !== '') {
            $lines[] = $this->indent($this->wrapText($group->description));
        }

        foreach ($group->options as $option) {
            $lines[] = $this->formatOption($option);
        }

        return implode("\n", $lines);
    }

    /**
     * Format single option.
     *
     * @param OptionConfig $option Option to format
     * @return string Formatted option
     */
    private function formatOption(OptionConfig $option): string
    {
        $opts = [];

        if ($this->shortFirst) {
            if ($option->short !== '') {
                $opts[] = $this->formatOptionString($option, true);
            }
            if ($option->long !== '') {
                $opts[] = $this->formatOptionString($option, false);
            }
        } else {
            if ($option->long !== '') {
                $opts[] = $this->formatOptionString($option, false);
            }
            if ($option->short !== '') {
                $opts[] = $this->formatOptionString($option, true);
            }
        }

        $optStr = implode(', ', $opts);
        $help = $option->help ?? '';

        // Add default value to help text if present
        if ($option->default !== null && !$option->action->isBoolean()) {
            $defaultStr = is_array($option->default)
                ? '[' . implode(', ', $option->default) . ']'
                : (string)$option->default;
            $help .= " (default: {$defaultStr})";
        }

        // Add choices to help text if present
        if (!empty($option->choices)) {
            $choiceStr = implode(', ', array_map(fn($c) => "'{$c}'", $option->choices));
            $help .= " (choices: {$choiceStr})";
        }

        return $this->formatOptionLine($optStr, $help);
    }

    /**
     * Format option string with argument placeholder.
     *
     * @param OptionConfig $option Option configuration
     * @param bool $short True for short format, false for long
     * @return string Formatted option string
     */
    private function formatOptionString(OptionConfig $option, bool $short): string
    {
        $opt = $short ? $option->short : $option->long;

        if (!$option->action->takesValue()) {
            return $opt;
        }

        $metavar = $option->metavar ?? strtoupper($option->getDestination());

        if ($short) {
            return "{$opt} {$metavar}";
        } else {
            return "{$opt}={$metavar}";
        }
    }

    /**
     * Format option line with help text.
     *
     * @param string $optStr Option string
     * @param string $help Help text
     * @return string Formatted line
     */
    private function formatOptionLine(string $optStr, string $help): string
    {
        $indent = str_repeat(' ', $this->indent);
        $optStr = $indent . $optStr;

        if (strlen($optStr) < $this->maxHelpPosition && $help !== '') {
            // Help on same line
            $padding = str_repeat(' ', $this->maxHelpPosition - strlen($optStr));
            $helpIndent = str_repeat(' ', $this->maxHelpPosition);
            $wrappedHelp = $this->wrapText(
                $help,
                $this->width - $this->maxHelpPosition,
                $helpIndent
            );
            return $optStr . $padding . $wrappedHelp;
        } elseif ($help !== '') {
            // Help on next line
            $helpIndent = str_repeat(' ', $this->maxHelpPosition);
            $wrappedHelp = $this->wrapText(
                $help,
                $this->width - $this->maxHelpPosition,
                $helpIndent
            );
            return $optStr . "\n" . $helpIndent . $wrappedHelp;
        } else {
            return $optStr;
        }
    }

    /**
     * Wrap text to terminal width.
     *
     * @param string $text Text to wrap
     * @param int|null $width Width (null = use default)
     * @param string $indent Indentation for wrapped lines
     * @return string Wrapped text
     */
    private function wrapText(string $text, ?int $width = null, string $indent = ''): string
    {
        $width ??= $this->width;

        if ($width <= 0 || strlen($text) <= $width) {
            return $text;
        }

        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            $lineLength = strlen($indent) + strlen($testLine);

            if ($lineLength > $width && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        if ($indent === '') {
            return implode("\n", $lines);
        }

        return implode("\n" . $indent, $lines);
    }

    /**
     * Indent text.
     *
     * @param string $text Text to indent
     * @param int $level Indent level (multiplied by indent)
     * @return string Indented text
     */
    private function indent(string $text, int $level = 1): string
    {
        $indent = str_repeat(' ', $this->indent * $level);
        $lines = explode("\n", $text);
        return implode("\n", array_map(fn($l) => $indent . $l, $lines));
    }

    /**
     * Get terminal width.
     *
     * @return int Width in characters
     */
    public function getWidth(): int
    {
        if ($this->width > 0) {
            return $this->width;
        }

        // Auto-detect terminal width
        $width = 80; // Default fallback

        if (function_exists('exec')) {
            $output = [];
            @exec('tput cols 2>/dev/null', $output);
            if (isset($output[0]) && is_numeric($output[0])) {
                $width = (int)$output[0];
            }
        }

        return $width;
    }

    /**
     * Format contexts section.
     *
     * @param array<ContextConfig> $contexts Contexts to format
     * @return string Formatted contexts section
     */
    private function formatContexts(array $contexts): string
    {
        $lines = ["Commands:"];

        foreach ($contexts as $context) {
            $lines[] = $this->formatContext($context);
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Format a single context.
     *
     * @param ContextConfig $context Context to format
     * @return string Formatted context line
     */
    private function formatContext(ContextConfig $context): string
    {
        $indent = str_repeat(' ', $this->indent);

        // Format context name with aliases
        $names = $context->name;
        if (!empty($context->aliases)) {
            $names .= ' (' . implode(', ', $context->aliases) . ')';
        }

        // Pad to max help position
        $nameWidth = $this->maxHelpPosition - $this->indent;
        $namePart = str_pad($names, $nameWidth);

        // Description
        $desc = $context->description;
        if ($desc === '') {
            return $indent . $names;
        }

        // Wrap description if needed
        $descWidth = $this->getWidth() - $this->maxHelpPosition;
        if (strlen($desc) > $descWidth) {
            $desc = wordwrap($desc, $descWidth, "\n" . str_repeat(' ', $this->maxHelpPosition));
        }

        return $indent . $namePart . $desc;
    }

    /**
     * Format help for a specific context.
     *
     * Generates help text for a single context, showing context-specific options.
     *
     * @param ParserConfig $config Parser configuration
     * @param ContextConfig $context Context to format
     * @param array<OptionConfig> $globalOptions Global options
     * @return string Formatted context help
     */
    public function formatContextHelp(
        ParserConfig $config,
        ContextConfig $context,
        array $globalOptions = []
    ): string {
        $parts = [];

        // Usage (context-specific if available)
        $usage = $context->usage !== '' ? $context->usage : $config->usage;
        if ($usage !== '' && $usage !== null) {
            $prog = $config->prog ?? 'program';
            $usage = str_replace('%prog', $prog . ' ' . $context->name, $usage);
            $parts[] = "Usage: {$usage}\n";
        }

        // Context description
        if ($context->description !== '') {
            $parts[] = $this->formatDescription($context->description);
        }

        // Context help (detailed)
        if ($context->help !== '') {
            $parts[] = $this->wrapText($context->help) . "\n";
        }

        // Context-specific options
        if (!empty($context->options)) {
            $parts[] = "Context Options:";
            $parts[] = $this->formatOptions($context->options);
        }

        // Global options (if any)
        if (!empty($globalOptions)) {
            $parts[] = "Global Options:";
            $parts[] = $this->formatOptions($globalOptions);
        }

        // Argument requirements
        if ($context->minArgs > 0 || $context->maxArgs < PHP_INT_MAX) {
            $argInfo = $this->formatArgumentRequirements($context);
            if ($argInfo !== '') {
                $parts[] = $argInfo;
            }
        }

        return implode("\n", array_filter($parts)) . "\n";
    }

    /**
     * Format argument requirements for a context.
     *
     * @param ContextConfig $context Context
     * @return string Formatted argument requirements
     */
    private function formatArgumentRequirements(ContextConfig $context): string
    {
        $parts = ["Arguments:"];
        $indent = str_repeat(' ', $this->indent);

        $desc = $context->argsDescription !== '' ? ': ' . $context->argsDescription : '';

        if ($context->minArgs === $context->maxArgs) {
            $parts[] = $indent . sprintf(
                "Requires exactly %d argument%s%s",
                $context->minArgs,
                $context->minArgs === 1 ? '' : 's',
                $desc
            );
        } elseif ($context->maxArgs === PHP_INT_MAX) {
            $parts[] = $indent . sprintf(
                "Requires at least %d argument%s%s",
                $context->minArgs,
                $context->minArgs === 1 ? '' : 's',
                $desc
            );
        } else {
            $parts[] = $indent . sprintf(
                "Accepts %d-%d arguments%s",
                $context->minArgs,
                $context->maxArgs,
                $desc
            );
        }

        return implode("\n", $parts);
    }
}
