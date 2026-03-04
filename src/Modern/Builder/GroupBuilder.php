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

namespace Horde\Argv\Modern\Builder;

use Horde\Argv\Modern\Config\{OptionConfig, OptionGroupConfig};

/**
 * Immutable builder for option groups.
 *
 * Following Principle #7: Immutable Throughout
 * Each method clones and returns new builder.
 *
 * Usage:
 *   $group = GroupBuilder::create('Advanced Options')
 *       ->withDescription('Advanced configuration options')
 *       ->addOption($debugOption)
 *       ->addOption($traceOption)
 *       ->build();
 *
 * @category Horde
 * @package  Argv
 */
class GroupBuilder
{
    private string $title;
    private string $description = '';
    private array $options = [];

    private function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * Create new group builder.
     *
     * @param string $title Group title
     * @return self
     */
    public static function create(string $title): self
    {
        return new self($title);
    }

    /**
     * Set group description.
     *
     * @param string $description Description text
     * @return self New builder instance
     */
    public function withDescription(string $description): self
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    /**
     * Add an option to the group.
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
     * Add multiple options to the group.
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
     * Build immutable group config.
     *
     * @return OptionGroupConfig Immutable group configuration
     */
    public function build(): OptionGroupConfig
    {
        return new OptionGroupConfig(
            title: $this->title,
            description: $this->description,
            options: $this->options
        );
    }
}
